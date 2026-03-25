<?php
require_once 'admin_guard.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("UPDATE spam_flags SET resolved=1 WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: spam.php");
exit;