<?php
session_start();
require "../db_connect.php";

if ($_SESSION['user_role'] !== 'landlord') {
    header("Location: ../index/index.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid booking.");
}

$booking_id = intval($_GET['id']);

// approve booking
$conn->query("UPDATE bookings SET status='approved' WHERE id=$booking_id");
$conn->query("UPDATE rooms SET status='occupied' WHERE id=(SELECT room_id FROM bookings WHERE id=$booking_id)");
// redirect back
header("Location: manage_booking.php");
exit;