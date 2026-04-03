<?php
session_start();
require_once "auth_student.php";
require_once "../includes/risk_engine.php";
include "../toast.php";

$student_internal_id = $_SESSION['user_id'];

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
      AND b.student_internal_id = ? 
      AND b.status = 'pending'
");
$checkStmt->bind_param("ii", $booking_id, $student_internal_id);
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


//log activity
$user_type = 'student';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");

$action = 'CANCEL_BOOKING';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();
//spam check
// Rapid action detection (Student)
$check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM activity_logs
    WHERE user_type='student'
    AND user_id=?
    AND action IN ('BOOK_ROOM','CANCEL_BOOKING')
    AND created_at > UTC_TIMESTAMP() - INTERVAL 2 MINUTE
");
$check->bind_param("i", $user_id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if ($count >= 3) {
             // risk score
    $user_type = 'student';
    $user_id   = $_SESSION['user_id'];

    addRisk($conn, $user_type, $user_id, 15);

    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='student'
        AND user_id=?
        AND reason='Suspicious rapid booking activity'
        AND created_at > UTC_TIMESTAMP() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {

        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('student', ?, 'Suspicious rapid booking activity', 'medium')
        ");
        $flag->bind_param("i", $user_id);
        $flag->execute();
    }
}

/* 4️⃣ Redirect back */
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Booking cancelled successfully.'
];
header("Location: view_room.php?house_id=$house_id&success=booking_cancelled");
exit;