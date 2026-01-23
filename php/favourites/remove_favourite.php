<?php
require '../session.php';
require '../db_connect.php';

if ($_SESSION['user_role'] !== 'student') {
    header("Location: ../index/index.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$room_id = intval($_GET['room_id']);
$room_id = (int) $_POST['room_id'];


$stmt = $conn->prepare(
    "DELETE FROM favourites WHERE student_id=? AND room_id=?"
);
$stmt->bind_param("si", $student_id, $room_id);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;