<?php
session_start();
include '../toast.php';
require_once 'admin_guard.php';
require '../db_connect.php';

$type = $_GET['type'];
$id   = intval($_GET['id']);

if ($type == 'landlord') {
    $stmt = $conn->prepare("UPDATE landlords SET risk_score=100 WHERE id=?");
}
elseif ($type == 'student') {
    $stmt = $conn->prepare("UPDATE students SET risk_score=100 WHERE id=?");
}
else {
    die("Invalid user type");
}

$stmt->bind_param("i", $id);
$stmt->execute();

// Also resolve spam flags for this user
$spam_stmt = $conn->prepare("UPDATE spam_flags SET resolved=1 WHERE user_type=? AND user_id=?");
$spam_stmt->bind_param("si", $type, $id);
$spam_stmt->execute();
    
$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'User suspended successfully.'
];
header("Location: spam.php");
exit;