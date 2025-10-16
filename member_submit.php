<?php
// member_submit.php
// Handles member application submissions, stores files and data, assigns a user ID.

// Configuration
$baseUpload = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'members' . DIRECTORY_SEPARATOR;
$csvFile = $baseUpload . 'submissions.csv';
$maxFileSize = 6 * 1024 * 1024; // 6MB
$allowedImage = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];

function render($title, $body){
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($title).'</title>';
    echo '<link rel="stylesheet" href="css/bootstrap.min.css">';
    echo '</head><body class="p-4"><div class="container"><div class="row justify-content-center"><div class="col-md-8">'.$body.'<p class="mt-3"><a href="member.html" class="btn btn-secondary">Back</a></p></div></div></div></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render('Invalid Method','<div class="alert alert-danger">Please submit the form.</div>');
}

$name = trim($_POST['name'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$aadhar = trim($_POST['aadhar'] ?? '');
$address = trim($_POST['address'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$email = trim($_POST['email'] ?? '');
$profession = trim($_POST['profession'] ?? '');
$blood = trim($_POST['blood'] ?? '');
$state = trim($_POST['state'] ?? '');
$district = trim($_POST['district'] ?? '');
$member_amount = trim($_POST['member_amount'] ?? '');
$member_utr = trim($_POST['member_utr'] ?? '');

$errors = [];
if ($name === '') $errors[] = 'Name is required.';
if ($dob === '') $errors[] = 'Date of birth is required.';
if ($mobile === '') $errors[] = 'Mobile is required.';
if ($aadhar === '') $errors[] = 'Aadhar is required.';
if ($member_amount === '' || !is_numeric($member_amount) || $member_amount <= 0) $errors[] = 'Valid donation amount required.';
if ($member_utr === '') $errors[] = 'UTR / transaction reference required.';

// screenshot required
if (!isset($_FILES['member_screenshot'])) $errors[] = 'Payment screenshot is required.';

if (!empty($errors)) {
    $html = '<div class="alert alert-danger"><h4>Submission errors</h4><ul>';
    foreach ($errors as $e) $html .= '<li>'.htmlspecialchars($e).'</li>';
    $html .= '</ul></div>';
    render('Errors', $html);
}

// generate user id and folder
try { $rand = bin2hex(random_bytes(3)); } catch(Exception $e) { $rand = dechex(mt_rand(100000,999999)); }
$userId = 'M'.time().$rand;
$userDir = $baseUpload . $userId . DIRECTORY_SEPARATOR;
if (!is_dir($userDir)) mkdir($userDir, 0755, true);

$savedProfile = '';
$savedIdDoc = '';
$savedOther = '';
$savedScreenshot = '';

// helper to move uploaded file and validate image types
function moveUploaded($fileKey, $destDir, $allowed, $maxSize, &$outName, &$errors){
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) { $outName = ''; return; }
    $f = $_FILES[$fileKey];
    if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = "Upload error for $fileKey: " . $f['error']; return; }
    if ($f['size'] > $maxSize) { $errors[] = "File $fileKey exceeds size limit."; return; }
    $mime = mime_content_type($f['tmp_name']);
    $ext = $allowed[$mime] ?? null;
    if (!$ext) { $errors[] = "File $fileKey has invalid type ($mime)."; return; }
    $safe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($f['name']));
    $name = time() . '_' . substr(md5($safe),0,8) . '.' . $ext;
    $dst = $destDir . $name;
    if (!move_uploaded_file($f['tmp_name'], $dst)) { $errors[] = "Failed to move uploaded file $fileKey."; return; }
    $outName = $name;
}

// Move files
moveUploaded('profile_pic', $userDir, $allowedImage, $maxFileSize, $savedProfile, $errors);
moveUploaded('id_doc', $userDir, $allowedImage, $maxFileSize, $savedIdDoc, $errors);
moveUploaded('other_doc', $userDir, $allowedImage, $maxFileSize, $savedOther, $errors);
moveUploaded('member_screenshot', $userDir, $allowedImage, $maxFileSize, $savedScreenshot, $errors);

if (!empty($errors)) {
    $html = '<div class="alert alert-danger"><h4>File upload errors</h4><ul>';
    foreach ($errors as $e) $html .= '<li>'.htmlspecialchars($e).'</li>';
    $html .= '</ul></div>';
    render('Upload Errors', $html);
}

// Save record to CSV
$row = [date('c'), $userId, $name, $dob, $mobile, $aadhar, $email, $address, $pincode, $profession, $blood, $state, $district, $savedProfile, $savedIdDoc, $savedOther, $member_amount, $member_utr, $savedScreenshot];
$fp = fopen($csvFile, 'a');
if ($fp) {
    if (ftell($fp) === 0) fputcsv($fp, ['timestamp','userid','name','dob','mobile','aadhar','email','address','pincode','profession','blood','state','district','profile_pic','id_doc','other_doc','amount','utr','screenshot']);
    fputcsv($fp, $row);
    fclose($fp);
} else {
    // non-fatal
}

// Show success with userid and note that password is DOB
$html = '<div class="alert alert-success"><h4>Application received</h4>';
$html .= '<p>Your membership application has been received. Please save these credentials to download your ID card:</p>';
$html .= '<ul class="list-group">';
$html .= '<li class="list-group-item"><strong>User ID:</strong> '.htmlspecialchars($userId).'</li>';
$html .= '<li class="list-group-item"><strong>Password (use Date of Birth):</strong> '.htmlspecialchars($dob).'</li>';
$html .= '<li class="list-group-item">After verification you can download your ID at <a href="idcard.html">ID Card download</a>.</li>';
$html .= '</ul>';
$html .= '<p class="mt-3">Store your User ID safely. The password is your Date of Birth as entered during application.</p>';
$html .= '</div>';

render('Application Received', $html);

?>
