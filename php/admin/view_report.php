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

    $reply = htmlspecialchars($_POST['reply']);

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

<h2>Report #<?= $report['id'] ?></h2>

<p><strong>Name:</strong> <?= htmlspecialchars($report['name']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($report['email']) ?></p>
<p><strong>Subject:</strong> <?= htmlspecialchars($report['subject']) ?></p>
<p><strong>Message:</strong></p>
<p><?= nl2br(htmlspecialchars($report['message'])) ?></p>

<hr>

<form method="POST">
<textarea name="reply" placeholder="Write admin reply..." required></textarea>
<button type="submit">Mark as Resolved</button>
</form>
</body>
</html> 