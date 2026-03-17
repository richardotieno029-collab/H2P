<?php
require_once 'admin001_guard.php';
require_once '../toast.php';
require_once '../db_connect.php';

$bookingId = null;
$action = null;
$password = null;

// Support return from verification step via session stored pending action.
if (isset($_GET['execute']) && $_GET['execute'] === '1' && isset($_SESSION['admin001_pending_action'])) {
    $pending = $_SESSION['admin001_pending_action'];
    if (($pending['handler'] ?? '') === 'booking_action') {
        $bookingId = $pending['booking_id'] ?? null;
        $action = $pending['action'] ?? null;
        // Use stored admin password hash (already verified as current admin) to proceed.
        $password = $_SESSION['admin_password_hash'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? $bookingId;
    $action = $_POST['action'] ?? $action;
    $password = $_POST['password'] ?? $password;
}

if (!$bookingId || !$action) {
    set_toast('Invalid request.', 'error');
    header('Location: bookings.php');
    exit;
}

if (!$bookingId || !$action) {
    set_toast('Invalid request.', 'error');
    header('Location: bookings.php');
    exit;
}

// Verify super-admin password for the action.
// If no password provided, require verification step.
if (!$password) {
    // Store pending action in session for use after verification.
    $_SESSION['admin001_pending_action'] = [
        'handler' => 'booking_action',
        'booking_id' => $bookingId,
        'action' => $action,
    ];
    header('Location: verify.php?return=booking_action.php');
    exit;
}

// Load admin password hash (fall back to DB lookup)
$hash = $_SESSION['admin_password_hash'] ?? '';
if (!$hash && isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare('SELECT password FROM admins WHERE admin_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'] ?? '';
    $stmt->close();
}

if (!password_verify($password, $hash)) {
    set_toast('Incorrect password. Action cancelled.', 'error');
    unset($_SESSION['admin001_pending_action']);
    header('Location: bookings.php');
    exit;
}

// Clear pending action after successful password validation.
unset($_SESSION['admin001_pending_action']);

$status = null;
if ($action === 'approve') {
    $status = 'approved';
} elseif ($action === 'reject') {
    $status = 'rejected';
}

if ($status) {
    $stmt = $conn->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $bookingId);
    $stmt->execute();
    $stmt->close();

    // Notify student when booking status changes
    require_once '../includes/mailer.php';

    $infoStmt = $conn->prepare(
        "SELECT b.student_internal_id, s.full_name, s.email, h.house_name, r.room_number
         FROM bookings b
         JOIN students s ON b.student_internal_id = s.id
         JOIN rooms r ON b.room_id = r.id
         JOIN houses h ON r.house_id = h.house_id
         WHERE b.id = ?"
    );
    $infoStmt->bind_param('i', $bookingId);
    $infoStmt->execute();
    $info = $infoStmt->get_result()->fetch_assoc();
    $infoStmt->close();

    if ($info && !empty($info['email'])) {
        $subject = "Your booking has been " . ($status === 'approved' ? 'approved' : 'rejected');
        $body = "Hi {$info['full_name']},<br><br>" .
            "Your booking request for room <strong>{$info['room_number']}</strong> at <strong>{$info['house_name']}</strong> has been <strong>{$status}</strong>.<br><br>" .
            "You can log in to your account to view more details.<br><br>" .
            "Thanks,<br>H2P Team";

        sendMailQuiet($info['email'], $info['full_name'], $subject, $body);
    }

    set_toast('Booking has been ' . ($status === 'approved' ? 'approved' : 'rejected') . '.', 'success');
}

header('Location: bookings.php');
exit;
