<?php
require_once 'admin_guard.php';
require_once '../db_connect.php';

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM reports WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    die("Report not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reply = $_POST['reply'];

    $update = $conn->prepare("UPDATE reports SET admin_reply=?, status='resolved' WHERE id=?");
    $update->bind_param("si", $reply, $id);
    $update->execute();

    header("Location: admin_reports.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin - Reports</title>
<link rel="stylesheet" href="admin_styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="admin-container">

    <div class="report-card">
        <div class="report-header">
            <h2><i class="fa-solid fa-flag"></i> Report #<?= $report['id'] ?></h2>
            <span class="status-badge"><?= htmlspecialchars($report['status']) ?></span>
        </div>

        <div class="report-info">
            <p><strong><i class="fa-solid fa-user"></i> Name:</strong> <?= htmlspecialchars($report['name']) ?></p>
            <p><strong><i class="fa-solid fa-envelope"></i> Email:</strong> <?= htmlspecialchars($report['email']) ?></p>
            <p><strong><i class="fa-solid fa-tag"></i> Subject:</strong> <?= htmlspecialchars($report['subject']) ?></p>
        </div>

        <div class="report-message">
            <h4><i class="fa-solid fa-comment"></i> Message</h4>
            <p><?= nl2br(htmlspecialchars($report['message'])) ?></p>
        </div>
    </div>

    <div class="reply-card">
        <h3><i class="fa-solid fa-reply"></i> Admin Response</h3>

        <form method="POST">
            <textarea name="reply" placeholder="Write admin reply..." required></textarea>
            <button type="submit">
                <i class="fa-solid fa-check"></i> Mark as Resolved
            </button>
        </form>
    </div>

</div>

</body>
</html> 