<?php
session_start();
require_once "auth_student.php";
require_once "../../includes/risk_engine.php";
include "../../toast.php";

$student_id = $_SESSION['user_id'];
$host_id = intval($_GET['host_id']);

/* ensure listing belongs to logged in student */

$stmt = $conn->prepare("
DELETE FROM roommate_hosts
WHERE host_id=? AND student_id=?
");

$stmt->bind_param("ii",$host_id,$student_id);
$stmt->execute();

/* delete pending requests for that listing */

$stmt = $conn->prepare("
DELETE FROM roommate_requests
WHERE host_id=?
");

$stmt->bind_param("i",$host_id);
$stmt->execute();

// Log activity
$user_type = 'student';
$user_id   = $student_id;
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");

$action = 'DELETE_ROOMMATE_LISTING';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();

// Spam check - rapid create/delete listing
$check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM activity_logs
    WHERE user_type='student'
      AND user_id=?
      AND action IN ('CREATE_ROOMMATE_LISTING','DELETE_ROOMMATE_LISTING')
      AND created_at > NOW() - INTERVAL 2 MINUTE
");
$check->bind_param("i", $user_id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if ($count >= 3) {
    addRisk($conn, $user_type, $user_id, 15);

    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='student'
          AND user_id=?
          AND reason='Suspicious rapid listing activity'
          AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('student', ?, 'Suspicious rapid listing activity', 'medium')
        ");
        $flag->bind_param("i", $user_id);
        $flag->execute();
    }
}

$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Listing deleted successfully.'
];

header("Location: roommate_quest.php");
exit();
?>