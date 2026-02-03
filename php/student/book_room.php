<?php
session_start();
require_once "../db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_POST['room_id'], $_POST['house_id'])) {
    die("Invalid request.");
}

$student_id = $_SESSION['user_id'];
$room_id    = (int) $_POST['room_id'];
$house_id   = (int) $_POST['house_id'];

/* 🔒 Prevent double booking */
$check = $conn->prepare("SELECT status FROM rooms WHERE id = ?");
$check->bind_param("i", $room_id);
$check->execute();
$status = $check->get_result()->fetch_assoc()['status'];

if ($status !== 'vacant') {
    header("Location: view_room.php?house_id=$house_id");
    exit;
}

/* 1️⃣ Insert booking */
$stmt = $conn->prepare("
    INSERT INTO bookings (student_id, room_id, status)
    VALUES (?, ?, 'pending')
");
$stmt->bind_param("si", $student_id, $room_id);
$stmt->execute();
// Get landlord id from house
$sql = "SELECT landlord_id FROM houses WHERE house_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $house_id);
$stmt->execute();
$house = $stmt->get_result()->fetch_assoc();

$landlord_id = $house['landlord_id'];

// Create notification
$msg = "New booking request received";
$notify = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
$stmt = $conn->prepare($notify);
$stmt->bind_param("is", $landlord_id, $msg);
$stmt->execute();

/* 2️⃣ Update room status */
$update = $conn->prepare("UPDATE rooms SET status='pending' WHERE id=?");
$update->bind_param("i", $room_id);
$update->execute();

/* 3️⃣ Redirect BACK WITH house_id */
header("Location: view_room.php?house_id=$house_id");
exit;
?>