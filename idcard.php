<?php
// idcard.php
// Validates a userid + dob (used as password) and returns a generated PNG ID card for download.

$baseUpload = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'members' . DIRECTORY_SEPARATOR;
$csvFile = $baseUpload . 'submissions.csv';

function sendError($msg){
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error</title><link rel="stylesheet" href="css/bootstrap.min.css"></head><body class="p-4"><div class="container"><div class="alert alert-danger">'.htmlspecialchars($msg).'</div><p><a href="idcard.html" class="btn btn-secondary">Back</a></p></div></body></html>';
    exit;
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

// found member; prepare data
$name = $found['name'] ?? '';
$profilePic = $found['profile_pic'] ?? '';
$idDoc = $found['id_doc'] ?? '';

$profilePath = $profilePic ? $baseUpload . $userid . DIRECTORY_SEPARATOR . $profilePic : '';

// Generate ID card image using GD
if (extension_loaded('gd')) {
    $width = 800; $height = 500;
    $im = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($im, 255,255,255);
    $black = imagecolorallocate($im, 0,0,0);
    $gray = imagecolorallocate($im, 230,230,230);
    imagefilledrectangle($im, 0, 0, $width, $height, $white);
    // header
    imagefilledrectangle($im, 0,0,$width,100,$gray);
    $fontFile = __DIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'arial.ttf';
    $title = 'Member ID Card';
    if (file_exists($fontFile)) {
        imagettftext($im, 28, 0, 30, 60, $black, $fontFile, $title);
    } else {
        imagestring($im, 5, 30, 40, $title, $black);
    }

    // draw profile pic if available
    if ($profilePath && file_exists($profilePath)) {
        $info = getimagesize($profilePath);
        $mime = $info['mime'] ?? '';
        $src = null;
        if ($mime === 'image/jpeg') $src = imagecreatefromjpeg($profilePath);
        elseif ($mime === 'image/png') $src = imagecreatefrompng($profilePath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = imagecreatefromwebp($profilePath);
        if ($src) {
            $thumbW = 240; $thumbH = 240;
            $dstX = 40; $dstY = 140;
            $tmp = imagecreatetruecolor($thumbW, $thumbH);
            // preserve transparency for png/webp
            imagefill($tmp,0,0,$white);
            imagecopyresampled($tmp, $src, 0,0,0,0, $thumbW, $thumbH, imagesx($src), imagesy($src));
            imagecopy($im, $tmp, $dstX, $dstY, 0,0, $thumbW, $thumbH);
            imagedestroy($tmp);
            imagedestroy($src);
        }
    }

    // write member details
    $startX = 320; $startY = 160;
    $lineGap = 48;
    $labelColor = imagecolorallocate($im, 50,50,50);
    $valueColor = imagecolorallocate($im, 10,10,10);
    $fontSize = 14;
    if (file_exists($fontFile)) {
        imagettftext($im, 16, 0, $startX, $startY, $labelColor, $fontFile, 'Name:');
        imagettftext($im, 16, 0, $startX+80, $startY, $valueColor, $fontFile, $name);
        imagettftext($im, 16, 0, $startX, $startY+$lineGap, $labelColor, $fontFile, 'User ID:');
        imagettftext($im, 16, 0, $startX+80, $startY+$lineGap, $valueColor, $fontFile, $userid);
        imagettftext($im, 16, 0, $startX, $startY+$lineGap*2, $labelColor, $fontFile, 'DOB:');
        imagettftext($im, 16, 0, $startX+80, $startY+$lineGap*2, $valueColor, $fontFile, $found['dob']);
        imagettftext($im, 16, 0, $startX, $startY+$lineGap*3, $labelColor, $fontFile, 'Phone:');
        imagettftext($im, 16, 0, $startX+80, $startY+$lineGap*3, $valueColor, $fontFile, $found['mobile']);
    } else {
        imagestring($im, 5, $startX, $startY-20, 'Name: '.$name, $valueColor);
        imagestring($im, 5, $startX, $startY+10, 'User ID: '.$userid, $valueColor);
        imagestring($im, 5, $startX, $startY+40, 'DOB: '.$found['dob'], $valueColor);
        imagestring($im, 5, $startX, $startY+70, 'Phone: '.$found['mobile'], $valueColor);
    }

    // footer
    imagestring($im, 3, 30, $height-40, 'Generated by Charifit', $gray);

    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="'. $userid . '_idcard.png"');
    imagepng($im);
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
