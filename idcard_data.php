<?php
// idcard_data.php
// Returns member data as JSON for client-side ID card preview

header('Content-Type: application/json');

$baseUpload = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'members' . DIRECTORY_SEPARATOR;
$csvFile = $baseUpload . 'submissions.csv';

function sendJSON($data) {
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['error' => 'Invalid request method']);
}

$action = $_POST['action'] ?? '';
if ($action !== 'fetch') {
    sendJSON(['error' => 'Invalid action']);
}

$userid = trim($_POST['userid'] ?? '');
$dob = trim($_POST['dob'] ?? '');

if ($userid === '' || $dob === '') {
    sendJSON(['error' => 'Please provide User ID and Date of Birth']);
}

if (!file_exists($csvFile)) {
    sendJSON(['error' => 'No member records found']);
}

$found = null;
$h = fopen($csvFile, 'r');
if (!$h) {
    sendJSON(['error' => 'Unable to open records']);
}

$headers = fgetcsv($h);
while (($row = fgetcsv($h)) !== false) {
    if (isset($row[1]) && $row[1] === $userid) {
        $data = [];
        foreach ($headers as $i => $col) {
            $data[$col] = $row[$i] ?? '';
        }
        $found = $data;
        break;
    }
}
fclose($h);

if (!$found) {
    sendJSON(['error' => 'User ID not found']);
}

// Verify password (DOB)
if ($found['dob'] !== $dob) {
    sendJSON(['error' => 'Invalid credentials. Please enter the Date of Birth used during application.']);
}

// Check if application is approved
$status = $found['status'] ?? 'pending';
if ($status !== 'approved') {
    sendJSON(['error' => 'Your application is currently under review (Status: ' . $status . '). Please wait for approval before downloading your ID card.']);
}

// Prepare response with profile picture URL if available
$profilePic = $found['profile_pic'] ?? '';
$profileUrl = '';
if ($profilePic) {
    $profilePath = 'uploads/members/' . rawurlencode($userid) . '/' . rawurlencode($profilePic);
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $profilePath)) {
        $profileUrl = $profilePath;
    }
}

// Return member data
sendJSON([
    'success' => true,
    'userid' => $found['userid'] ?? '',
    'name' => $found['name'] ?? '',
    'gender' => $found['gender'] ?? '',
    'sonof' => $found['sonof'] ?? '',
    'dob' => $found['dob'] ?? '',
    'mobile' => $found['mobile'] ?? '',
    'blood' => $found['blood'] ?? '',
    'address' => $found['address'] ?? '',
    'state' => $found['state'] ?? '',
    'district' => $found['district'] ?? '',
    'pincode' => $found['pincode'] ?? '',
    'email' => $found['email'] ?? '',
    'profession' => $found['profession'] ?? '',
    'profile_pic' => $profileUrl
]);
?>
