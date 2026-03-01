<?php
require_once "auth_student.php";
require_once "../db_connect.php";
include "../toast.php";

if (!isset($_POST['room_id'], $_POST['house_id'])) {
    die("Invalid request.");
}

$student_internal_id = $_SESSION['user_id'];
$room_id    = (int) $_POST['room_id'];
$house_id   = (int) $_POST['house_id'];

/* 🔒 Prevent double booking */
$check = $conn->prepare("SELECT status FROM rooms WHERE id = ?");
$check->bind_param("i", $room_id);
$check->execute();
$status = $check->get_result()->fetch_assoc()['status'];

if ($status !== 'vacant') {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Room is not available.'
];
    header("Location: view_room.php?house_id=$house_id");
    exit;
}
// Check if student already has a pending booking
$checkStmt = $conn->prepare("
    SELECT id 
    FROM bookings 
    WHERE student_internal_id = ? AND status = 'pending'
    LIMIT 1
");
$checkStmt->bind_param("s", $student_internal_id);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();

if ($existing) {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'You already have a pending booking.'
];
    header("Location: view_room.php?house_id=$house_id&error=one_pending");
    exit;
}

/* 1️⃣ Insert booking */
$insert = $conn->prepare("
    INSERT INTO bookings (student_internal_id, room_id, status, created_at, expires_at)
    VALUES (?, ?, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
");
$insert->bind_param("ii", $student_internal_id, $room_id);
$insert->execute();
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
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Room booked successfully! Awaiting landlord approval.'
];
header("Location: view_room.php?house_id=$house_id");
exit;
?>