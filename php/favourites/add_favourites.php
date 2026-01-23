<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

if (empty($_POST['room_id'])) {
    die('Room ID missing');
}

$student_id = $_SESSION['user_id'];
$room_id = (int) $_POST['room_id'];

$stmt = $conn->prepare("
    INSERT INTO favourites (student_id, room_id)
    VALUES (?, ?)
");
$stmt->bind_param("si", $student_id, $room_id);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;