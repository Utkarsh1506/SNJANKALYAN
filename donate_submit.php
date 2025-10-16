<?php
// donate_submit.php
// Simple server-side handler for donation form

// Configuration
$uploadBase = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'donations' . DIRECTORY_SEPARATOR;
$maxFileSize = 5 * 1024 * 1024; // 5 MB
$allowedMime = [
    'image/jpeg' => 'jpg',
    'image/jpg'  => 'jpg',
    'image/png'  => 'png'
];
$logCsv = $uploadBase . 'submissions.csv';

// Helper: render a simple HTML response
function renderPage($title, $bodyHtml) {
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title>';
    echo '<link rel="stylesheet" href="css/bootstrap.min.css">';
    echo '</head><body class="p-4">';
    echo '<div class="container"><div class="row justify-content-center"><div class="col-md-8">';
    echo $bodyHtml;
    echo '<p class="mt-3"><a href="donate.html" class="btn btn-secondary">Back to donation page</a></p>';
    echo '</div></div></div></body></html>';
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderPage('Invalid Method', '<div class="alert alert-danger">This endpoint accepts POST requests only.</div>');
}

// Basic required fields
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
$utr = isset($_POST['utr']) ? trim($_POST['utr']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

$errors = [];
if ($name === '') $errors[] = 'Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($phone === '') $errors[] = 'Phone/mobile is required.';
if ($amount === '' || !is_numeric($amount) || floatval($amount) <= 0) $errors[] = 'A valid donation amount is required.';
if ($utr === '') $errors[] = 'UTR / transaction reference is required.';

// File upload validation
if (!isset($_FILES['screenshot'])) {
    $errors[] = 'Payment screenshot is required.';
} else {
    $file = $_FILES['screenshot'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading the screenshot. Code: ' . $file['error'];
    } else {
        if ($file['size'] > $maxFileSize) {
            $errors[] = 'Screenshot exceeds the maximum allowed size of 5 MB.';
        } else {
            // Determine real mime type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!array_key_exists($mime, $allowedMime)) {
                $errors[] = 'Invalid screenshot file type. Only JPG and PNG allowed.';
            }
        }
    }
}

if (!empty($errors)) {
    $html = '<div class="alert alert-danger"><h4>Submission error</h4><ul>';
    foreach ($errors as $e) $html .= '<li>' . htmlspecialchars($e) . '</li>';
    $html .= '</ul></div>';
    renderPage('Submission Error', $html);
}

// Ensure upload directory exists
if (!is_dir($uploadBase)) {
    if (!mkdir($uploadBase, 0755, true)) {
        renderPage('Server Error', '<div class="alert alert-danger">Could not create upload directory.</div>');
    }
}

// Move uploaded file to uploads/donations
$ext = $allowedMime[$mime];
$safeFileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$destination = $uploadBase . $safeFileName;
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    renderPage('Server Error', '<div class="alert alert-danger">Failed to save uploaded file.</div>');
}

// Log submission to CSV
$logRow = [
    date('c'),
    $name,
    $email,
    $phone,
    $amount,
    $utr,
    $note,
    'file:' . $safeFileName
];

$fp = fopen($logCsv, 'a');
if ($fp) {
    // If file newly created, add header
    if (ftell($fp) === 0) {
        fputcsv($fp, ['timestamp','name','email','phone','amount','utr','note','screenshot_file']);
    }
    fputcsv($fp, $logRow);
    fclose($fp);
} else {
    // Not fatal — still show success, but warn
    $logWarning = '<div class="alert alert-warning">Warning: Could not write to submissions log.</div>';
}

// Render success page
$out = '<div class="alert alert-success"><h4>Thank you — Donation submitted</h4>';
$out .= '<p>We have received your submission. Our team will verify the transaction and contact you if needed.</p>';
$out .= '<ul class="list-group">';
$out .= '<li class="list-group-item"><strong>Name:</strong> ' . htmlspecialchars($name) . '</li>';
$out .= '<li class="list-group-item"><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>';
$out .= '<li class="list-group-item"><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</li>';
$out .= '<li class="list-group-item"><strong>Amount:</strong> ₹' . htmlspecialchars($amount) . '</li>';
$out .= '<li class="list-group-item"><strong>UTR:</strong> ' . htmlspecialchars($utr) . '</li>';
$out .= '<li class="list-group-item"><strong>Screenshot file:</strong> ' . htmlspecialchars($safeFileName) . '</li>';
$out .= '</ul>';
if (!empty($logWarning)) $out .= $logWarning;
$out .= '</div>';

renderPage('Donation Submitted', $out);

?>
