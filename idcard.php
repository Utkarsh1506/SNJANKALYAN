<?php
// idcard.php
// Validates a userid + dob (used as password) and returns a generated PNG ID card for download.

$baseUpload = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'members' . DIRECTORY_SEPARATOR;
$csvFile = $baseUpload . 'submissions.csv';

function sendError($msg){
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="p-4"><div class="container"><div class="alert alert-danger">'.htmlspecialchars($msg).'</div><p><a href="idcard.html" class="btn btn-secondary">Back</a></p></div></body></html>';
    exit;
}

function wordWrap($image, $fontFile, $fontSize, $text, $maxWidth) {
    $words = explode(' ', $text);
    $lines = [];
    $currentLine = '';
    
    foreach ($words as $word) {
        $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $testLine);
        $width = $bbox[2] - $bbox[0];
        
        if ($width <= $maxWidth) {
            $currentLine = $testLine;
        } else {
            if ($currentLine) $lines[] = $currentLine;
            $currentLine = $word;
        }
    }
    if ($currentLine) $lines[] = $currentLine;
    return $lines;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Invalid request method.');

$userid = trim($_POST['userid'] ?? '');
$dob = trim($_POST['dob'] ?? '');
if ($userid === '' || $dob === '') sendError('Please provide User ID and Date of Birth.');

if (!file_exists($csvFile)) sendError('No member records found.');

$found = null;
$h = fopen($csvFile,'r');
if (!$h) sendError('Unable to open records.');
$headers = fgetcsv($h);
while (($row = fgetcsv($h)) !== false) {
    // find by userid
    if (isset($row[1]) && $row[1] === $userid) {
        // map headers
        $data = [];
        foreach ($headers as $i => $col) $data[$col] = $row[$i] ?? '';
        $found = $data;
        break;
    }
}
fclose($h);

if (!$found) sendError('User ID not found.');

// Password is DOB as entered during application — compare exact string
if ($found['dob'] !== $dob) sendError('Invalid credentials. Please enter the Date of Birth used during application.');

// Check if application is approved
$status = $found['status'] ?? 'pending';
if ($status !== 'approved') {
    sendError('Your application is currently under review (Status: ' . htmlspecialchars($status) . '). Please wait for approval before downloading your ID card.');
}

// found member; prepare data
$name = $found['name'] ?? '';
$gender = $found['gender'] ?? '';
$sonof = $found['sonof'] ?? '';
$mobile = $found['mobile'] ?? '';
$blood = $found['blood'] ?? '';
$address = $found['address'] ?? '';
$state = $found['state'] ?? '';
$district = $found['district'] ?? '';
$profilePic = $found['profile_pic'] ?? '';

$profilePath = $profilePic ? $baseUpload . $userid . DIRECTORY_SEPARATOR . $profilePic : '';

// Generate ID card image using GD
if (extension_loaded('gd')) {
    // ID Card dimensions (standard credit card ratio 3.5:2)
    $width = 1050;  // 3.5 inches at 300 DPI
    $height = 600;  // 2 inches at 300 DPI
    
    $im = imagecreatetruecolor($width, $height);
    
    // Define colors
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    $headerBg = imagecolorallocate($im, 41, 128, 185);  // Professional blue
    $accentColor = imagecolorallocate($im, 52, 152, 219);  // Light blue
    $textDark = imagecolorallocate($im, 44, 62, 80);  // Dark gray
    $textLight = imagecolorallocate($im, 127, 140, 141);  // Light gray
    $borderColor = imagecolorallocate($im, 189, 195, 199);  // Border gray
    
    // Fill background
    imagefilledrectangle($im, 0, 0, $width, $height, $white);
    
    // Draw border
    imagerectangle($im, 5, 5, $width-6, $height-6, $borderColor);
    imagerectangle($im, 6, 6, $width-7, $height-7, $borderColor);
    
    // Header section with gradient-like effect
    imagefilledrectangle($im, 0, 0, $width, 120, $headerBg);
    
    // Check for system fonts (common paths)
    $fontPaths = [
        __DIR__ . '/fonts/arial.ttf',
        'C:/Windows/Fonts/arial.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/System/Library/Fonts/Helvetica.ttc'
    ];
    
    $fontFile = null;
    foreach ($fontPaths as $path) {
        if (file_exists($path)) {
            $fontFile = $path;
            break;
        }
    }
    
    // Organization name in header
    if ($fontFile) {
        imagettftext($im, 32, 0, 30, 65, $white, $fontFile, 'SNJANKALYAN');
        imagettftext($im, 12, 0, 30, 95, $white, $fontFile, 'Jan Kalyan Samiti - Member ID Card');
    } else {
        imagestring($im, 5, 30, 35, 'SNJANKALYAN', $white);
        imagestring($im, 3, 30, 70, 'Jan Kalyan Samiti - Member ID Card', $white);
    }
    
    // Profile photo section
    $photoX = 40;
    $photoY = 150;
    $photoWidth = 180;
    $photoHeight = 220;
    
    // Draw photo placeholder border
    imagerectangle($im, $photoX-2, $photoY-2, $photoX+$photoWidth+2, $photoY+$photoHeight+2, $borderColor);
    
    if ($profilePath && file_exists($profilePath)) {
        $info = @getimagesize($profilePath);
        if ($info) {
            $mime = $info['mime'] ?? '';
            $src = null;
            
            if ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($profilePath);
            elseif ($mime === 'image/png') $src = @imagecreatefrompng($profilePath);
            elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($profilePath);
            
            if ($src) {
                $tmp = imagecreatetruecolor($photoWidth, $photoHeight);
                imagefill($tmp, 0, 0, $white);
                
                // Calculate aspect ratio
                $srcW = imagesx($src);
                $srcH = imagesy($src);
                $ratio = min($photoWidth / $srcW, $photoHeight / $srcH);
                $newW = $srcW * $ratio;
                $newH = $srcH * $ratio;
                $offsetX = ($photoWidth - $newW) / 2;
                $offsetY = ($photoHeight - $newH) / 2;
                
                imagecopyresampled($tmp, $src, $offsetX, $offsetY, 0, 0, $newW, $newH, $srcW, $srcH);
                imagecopy($im, $tmp, $photoX, $photoY, 0, 0, $photoWidth, $photoHeight);
                imagedestroy($tmp);
                imagedestroy($src);
            }
        }
    } else {
        // No photo placeholder
        imagefilledrectangle($im, $photoX, $photoY, $photoX+$photoWidth, $photoY+$photoHeight, imagecolorallocate($im, 240, 240, 240));
        if ($fontFile) {
            imagettftext($im, 12, 0, $photoX+45, $photoY+110, $textLight, $fontFile, 'No Photo');
        } else {
            imagestring($im, 3, $photoX+55, $photoY+105, 'No Photo', $textLight);
        }
    }
    
    // Member details section
    $detailsX = 260;
    $detailsY = 160;
    $lineHeight = 45;
    $labelSize = 14;
    $valueSize = 16;
    
    if ($fontFile) {
        // Name (larger font)
        imagettftext($im, 11, 0, $detailsX, $detailsY, $textLight, $fontFile, 'Name:');
        imagettftext($im, 20, 0, $detailsX, $detailsY + 30, $textDark, $fontFile, strtoupper($name));
        
        // User ID
        $y = $detailsY + 75;
        imagettftext($im, $labelSize, 0, $detailsX, $y, $textLight, $fontFile, 'Member ID:');
        imagettftext($im, $valueSize, 0, $detailsX + 130, $y, $textDark, $fontFile, $userid);
        
        // Gender & Blood Group (side by side)
        $y += $lineHeight;
        imagettftext($im, $labelSize, 0, $detailsX, $y, $textLight, $fontFile, 'Gender:');
        imagettftext($im, $valueSize, 0, $detailsX + 130, $y, $textDark, $fontFile, $gender);
        imagettftext($im, $labelSize, 0, $detailsX + 350, $y, $textLight, $fontFile, 'Blood:');
        imagettftext($im, $valueSize, 0, $detailsX + 450, $y, $textDark, $fontFile, $blood);
        
        // Date of Birth
        $y += $lineHeight;
        imagettftext($im, $labelSize, 0, $detailsX, $y, $textLight, $fontFile, 'Date of Birth:');
        imagettftext($im, $valueSize, 0, $detailsX + 130, $y, $textDark, $fontFile, $dob);
        
        // Mobile
        $y += $lineHeight;
        imagettftext($im, $labelSize, 0, $detailsX, $y, $textLight, $fontFile, 'Mobile:');
        imagettftext($im, $valueSize, 0, $detailsX + 130, $y, $textDark, $fontFile, $mobile);
        
        // Address (with word wrap)
        $y += $lineHeight;
        imagettftext($im, $labelSize, 0, $detailsX, $y, $textLight, $fontFile, 'Address:');
        $addressLines = wordWrap($im, $fontFile, 13, $address . ', ' . $district . ', ' . $state, 450);
        $addrY = $y;
        foreach ($addressLines as $line) {
            imagettftext($im, 13, 0, $detailsX + 130, $addrY, $textDark, $fontFile, $line);
            $addrY += 25;
        }
        
        // Footer
        imagettftext($im, 10, 0, 30, $height - 25, $textLight, $fontFile, 'Issued: ' . date('d-M-Y'));
        imagettftext($im, 10, 0, $width - 280, $height - 25, $textLight, $fontFile, 'www.snjankalyan.org');
        
    } else {
        // Fallback without TTF fonts
        imagestring($im, 4, $detailsX, $detailsY, 'Name: ' . strtoupper($name), $textDark);
        imagestring($im, 3, $detailsX, $detailsY + 30, 'Member ID: ' . $userid, $textDark);
        imagestring($im, 3, $detailsX, $detailsY + 50, 'Gender: ' . $gender . '  Blood: ' . $blood, $textDark);
        imagestring($im, 3, $detailsX, $detailsY + 70, 'DOB: ' . $dob, $textDark);
        imagestring($im, 3, $detailsX, $detailsY + 90, 'Mobile: ' . $mobile, $textDark);
        imagestring($im, 2, $detailsX, $detailsY + 110, 'Address: ' . substr($address, 0, 50), $textDark);
        imagestring($im, 2, 30, $height - 20, 'Issued: ' . date('d-M-Y'), $textLight);
    }

    // Output ID card
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="idcard_' . $userid . '.png"');
    imagepng($im, null, 9);
    imagedestroy($im);
    exit;
}

// Fallback: show member details and links to uploaded images for manual download
?><!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ID Card</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="p-4"><div class="container"><h3>Member Details</h3><table class="table table-bordered"><tr><th>User ID</th><td><?php echo htmlspecialchars($userid); ?></td></tr><tr><th>Name</th><td><?php echo htmlspecialchars($name); ?></td></tr><tr><th>DOB</th><td><?php echo htmlspecialchars($found['dob']); ?></td></tr></table>
<?php if ($profilePath && file_exists($profilePath)): ?>
  <p><strong>Profile Photo:</strong> <a href="<?php echo 'uploads/members/'.rawurlencode($userid).'/'.rawurlencode($profilePic); ?>" download>Download</a></p>
<?php endif; ?>
<p class="mt-3"><a href="idcard.html" class="btn btn-secondary">Back</a></p></div></body></html>
