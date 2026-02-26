<?php
session_start();
require_once "../db_connect.php";
include "../toast.php";

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = [
    'type' => 'warning',
    'message' => 'UNKNOWN USER.'
];
    header("Location: ../student/login_form.php");
    exit;
}

$student_id = $_SESSION['user_id'];

if (!isset($_POST['booking_id'], $_POST['room_id'],)) {
    die("Invalid request.");
}

$booking_id = (int) $_POST['booking_id'];

/* 1️⃣ Verify booking belongs to student & is pending */
$checkStmt = $conn->prepare("
    SELECT b.id, b.room_id, r.house_id
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.id = ? 
      AND b.student_id = ? 
      AND b.status = 'pending'
");
$checkStmt->bind_param("ii", $booking_id, $student_id);
$checkStmt->execute();
$booking = $checkStmt->get_result()->fetch_assoc();

if (!$booking) {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Invalid booking cancellation request.'
];
    header("Location: view_room.php?house_id=$house_id&error=invalid_cancel");
    exit;
}
$room_id = $booking['room_id'];
$house_id = $booking['house_id'];

/* 2️⃣ Cancel booking */
$cancelStmt = $conn->prepare("
    UPDATE bookings 
    SET status = 'cancelled'
    WHERE id = ?
");
$cancelStmt->bind_param("i", $booking_id);
$cancelStmt->execute();

/* 3️⃣ Free the room */
$roomStmt = $conn->prepare("
    UPDATE rooms 
    SET status = 'vacant'
    WHERE id = ?
");
$roomStmt->bind_param("i", $room_id);
$roomStmt->execute();

/* 4️⃣ Redirect back */
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Booking cancelled successfully.'
];
header("Location: view_room.php?house_id=$house_id&success=booking_cancelled");
exit;