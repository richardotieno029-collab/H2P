<?php
require_once "auth_landlord.php";
require "../db_connect.php";
include "../toast.php";

/* =========================
   2️⃣ Validate booking ID
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid booking.");
}

$booking_id = (int) $_GET['id'];

/* =========================
   3️⃣ Get student + room from booking
========================= */
$stmt = $conn->prepare("
    SELECT student_internal_id, room_id, status
    FROM bookings 
    WHERE id = ? AND status = 'pending'
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found or expired.");
}

$booking    = $result->fetch_assoc();
$student_id = $booking['student_internal_id'];
$room_id    = $booking['room_id'];

/* =========================
   4️⃣ Approve booking
========================= */
$approve = $conn->prepare("
    UPDATE bookings 
    SET status = 'approved',
        approved_expires_at = DATE_ADD(NOW(), INTERVAL 12 HOUR)
    WHERE id = ?
");
$approve->bind_param("i", $booking_id);
$approve->execute();

/* =========================
   5️⃣ Mark room as occupied
========================= */
$occupy = $conn->prepare("
    UPDATE rooms 
    SET status = 'occupied'
    WHERE id = ?
");
$occupy->bind_param("i", $room_id);
$occupy->execute();

/* =========================
   6️⃣ Notify student
========================= */
$message = "Your booking has been approved! You can now contact the landlord.";

$notify = $conn->prepare("
    INSERT INTO notifications (user_id, message)
    VALUES (?, ?)
");
$notify->bind_param("is", $student_id, $message);
$notify->execute();

/* =========================
   7️⃣ Redirect back
========================= */
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Booking approved successfully.'
];

header("Location: manage_booking.php");
exit;