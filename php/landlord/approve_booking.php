<?php
require_once "auth_landlord.php";
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
// Notify student by email (non-blocking)
require_once "../includes/mailer.php";

$infoStmt = $conn->prepare("
SELECT 
    s.full_name AS student_name,
    s.email AS student_email,
    h.house_name,
    r.room_number

FROM bookings b

JOIN students s ON b.student_internal_id = s.id
JOIN rooms r ON b.room_id = r.id
JOIN houses h ON r.house_id = h.house_id

WHERE b.id = ?
");

$infoStmt->bind_param("i", $booking_id);
$infoStmt->execute();
$info = $infoStmt->get_result()->fetch_assoc();
$infoStmt->close();


if ($info && !empty($info['student_email'])) {

    $subject = "Booking Approved - {$info['house_name']}";

    $body = "Hello {$info['student_name']},<br><br>" .

        "Your booking request for <strong>Room {$info['room_number']}</strong> " .
        "in <strong>{$info['house_name']}</strong> has been <strong style='color:green;'>approved</strong>.<br><br>" .

        "Please log in to your account to contact the landlord and confirm that you have secured the room.<br><br>" .

        "Best regards,<br>" .
        "H2P Team";

    sendMailQuiet(
        $info['student_email'],
        $info['student_name'],
        $subject,
        $body
    );
}

//log activity
$user_type = 'landlord';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");
$action = 'APPROVE_BOOKING';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();

/* =========================
   7️⃣ Redirect back
========================= */
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Booking approved successfully.'
];

$redirect = $_GET['return'] ?? 'manage_booking.php';
$redirect = filter_var($redirect, FILTER_SANITIZE_URL);
header("Location: $redirect");
exit;