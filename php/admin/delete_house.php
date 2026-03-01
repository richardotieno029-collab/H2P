<?php
session_start();
require_once "admin_guard.php";
require '../db_connect.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$house_id = intval($_GET['id']);

// First delete rooms under house
$stmt = $conn->prepare("DELETE FROM rooms WHERE house_id=?");
$stmt->bind_param("i", $house_id);
$stmt->execute();

// Then delete house
$stmt = $conn->prepare("DELETE FROM houses WHERE house_id=?");
$stmt->bind_param("i", $house_id);
$stmt->execute();

$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'House and its rooms deleted successfully.'
];
header("Location: dashboard.php");
exit;