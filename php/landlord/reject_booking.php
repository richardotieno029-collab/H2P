<?php
session_start();
require_once "../db_connect.php";

// 1️⃣ Security check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord') {
    header("Location: ../index/index.php");
    exit;
}

// 2️⃣ Validate booking id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid booking.");
}

$booking_id = intval($_GET['id']);

// 3️⃣ Get room_id linked to this booking
$sql = "SELECT room_id FROM bookings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found.");
}

$row = $result->fetch_assoc();
$room_id = $row['room_id'];

// 4️⃣ Reject booking
$reject = "UPDATE bookings SET status = 'rejected' WHERE id = ?";
$stmt = $conn->prepare($reject);
$stmt->bind_param("i", $booking_id);
$stmt->execute();

// 5️⃣ Set room back to vacant
$roomUpdate = "UPDATE rooms SET status = 'vacant' WHERE id = ?";
$stmt = $conn->prepare($roomUpdate);
$stmt->bind_param("i", $room_id);
$stmt->execute();

// 6️⃣ Redirect back
header("Location: manage_booking.php");
exit;