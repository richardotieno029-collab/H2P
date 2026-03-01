<?php
session_start();
require '../db_connect.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$house_id = intval($_GET['id']);

/* First delete rooms under house
$stmt = $conn->prepare("DELETE FROM rooms WHERE house_id=?");
$stmt->bind_param("i", $house_id);
$stmt->execute();*/

// Then delete house
$stmt = $conn->prepare("UPDATE houses SET status='suspended' WHERE id=?WHERE id=?");
$stmt->bind_param("i", $house_id);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;