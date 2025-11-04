<?php
// admin_members.php
// Simple admin panel to view and approve/reject member applications
// TODO: Add proper authentication in production

$baseUpload = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'members' . DIRECTORY_SEPARATOR;
$csvFile = $baseUpload . 'submissions.csv';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $userid = $_POST['userid'] ?? '';
    $newStatus = $_POST['status'] ?? '';
    
    if ($userid && in_array($newStatus, ['pending', 'approved', 'rejected'])) {
        updateMemberStatus($userid, $newStatus);
        header('Location: admin_members.php?msg=updated');
        exit;
    }
}

function updateMemberStatus($userid, $newStatus) {
    global $csvFile;
    
    if (!file_exists($csvFile)) return false;
    
    $rows = [];
    $h = fopen($csvFile, 'r');
    $headers = fgetcsv($h);
    $rows[] = $headers;
    
    // Find status column index
    $statusIdx = array_search('status', $headers);
    $useridIdx = array_search('userid', $headers);
    
    while (($row = fgetcsv($h)) !== false) {
        if ($useridIdx !== false && isset($row[$useridIdx]) && $row[$useridIdx] === $userid) {
            if ($statusIdx !== false) {
                $row[$statusIdx] = $newStatus;
            }
        }
        $rows[] = $row;
    }
    fclose($h);
    
    // Write back
    $h = fopen($csvFile, 'w');
    foreach ($rows as $row) {
        fputcsv($h, $row);
    }
    fclose($h);
    
    return true;
}

// Read all members
$members = [];
if (file_exists($csvFile)) {
    $h = fopen($csvFile, 'r');
    $headers = fgetcsv($h);
    while (($row = fgetcsv($h)) !== false) {
        $data = [];
        foreach ($headers as $i => $col) {
            $data[$col] = $row[$i] ?? '';
        }
        $members[] = $data;
    }
    fclose($h);
}

// Sort by timestamp descending (newest first)
usort($members, function($a, $b) {
    return strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? '');
});

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Applications - Admin Panel</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        .status-badge { padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.875rem; }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
        .member-photo { max-width: 60px; max-height: 60px; border: 1px solid #ddd; }
        .table-actions { white-space: nowrap; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">SNJANKALYAN - Admin Panel</span>
            <a href="index.html" class="btn btn-light btn-sm">Back to Site</a>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Member status updated successfully!
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col">
                <h2>Member Applications</h2>
                <p class="text-muted">Review and approve/reject member applications</p>
            </div>
        </div>

        <?php if (empty($members)): ?>
            <div class="alert alert-info">No member applications found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Date</th>
                            <th>User ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>DOB</th>
                            <th>Mobile</th>
                            <th>Blood</th>
                            <th>Location</th>
                            <th>Amount</th>
                            <th>UTR</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <?php
                                $timestamp = $member['timestamp'] ?? '';
                                $date = '';
                                if ($timestamp) {
                                    try {
                                        $dt = new DateTime($timestamp);
                                        $date = $dt->format('d-M-Y H:i');
                                    } catch (Exception $e) {
                                        $date = substr($timestamp, 0, 16);
                                    }
                                }
                                $status = $member['status'] ?? 'pending';
                                $statusClass = 'status-' . $status;
                            ?>
                            <tr>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($date) ?></td>
                                <td><strong><?= htmlspecialchars($member['userid'] ?? '') ?></strong></td>
                                <td>
                                    <?php if (!empty($member['profile_pic'])): ?>
                                        <?php $imgPath = 'uploads/members/' . rawurlencode($member['userid']) . '/' . rawurlencode($member['profile_pic']); ?>
                                        <?php if (file_exists(__DIR__ . '/' . $imgPath)): ?>
                                            <img src="<?= $imgPath ?>" class="member-photo" alt="Photo">
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($member['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($member['gender'] ?? '') ?></td>
                                <td><?= htmlspecialchars($member['dob'] ?? '') ?></td>
                                <td><?= htmlspecialchars($member['mobile'] ?? '') ?></td>
                                <td><?= htmlspecialchars($member['blood'] ?? '') ?></td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars(($member['district'] ?? '') . ', ' . ($member['state'] ?? '')) ?></td>
                                <td>₹<?= htmlspecialchars($member['amount'] ?? '0') ?></td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($member['utr'] ?? '') ?></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td class="table-actions">
                                    <?php if ($status !== 'approved'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="userid" value="<?= htmlspecialchars($member['userid']) ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($status !== 'rejected'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="userid" value="<?= htmlspecialchars($member['userid']) ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Reject">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($status !== 'pending'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="userid" value="<?= htmlspecialchars($member['userid']) ?>">
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="btn btn-warning btn-sm" title="Mark as Pending">
                                                <i class="fa fa-clock-o"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="view_member.php?userid=<?= urlencode($member['userid']) ?>" class="btn btn-info btn-sm" title="View Details">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Applications</h5>
                            <h2><?= count($members) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="text-warning"><?= count(array_filter($members, fn($m) => ($m['status'] ?? 'pending') === 'pending')) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Approved</h5>
                            <h2 class="text-success"><?= count(array_filter($members, fn($m) => ($m['status'] ?? '') === 'approved')) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Rejected</h5>
                            <h2 class="text-danger"><?= count(array_filter($members, fn($m) => ($m['status'] ?? '') === 'rejected')) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/vendor/jquery-1.12.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
