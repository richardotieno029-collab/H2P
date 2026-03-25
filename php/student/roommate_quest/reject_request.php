<?php
session_start();
require_once "auth_student.php";
require_once "../../includes/risk_engine.php";
include "../../toast.php";

$request_id = intval($_GET['id']);

/* remove request */

$stmt = $conn->prepare("
DELETE FROM roommate_requests
WHERE request_id=?
");

$stmt->bind_param("i",$request_id);
$stmt->execute();

// Log activity
$user_type = 'student';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");

$action = 'REJECT_ROOMMATE_REQUEST';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();

// Spam check - rapid approve/reject actions
$check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM activity_logs
    WHERE user_type='student'
      AND user_id=?
      AND action IN ('APPROVE_ROOMMATE_REQUEST','REJECT_ROOMMATE_REQUEST')
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
          AND reason='Suspicious rapid roommate decision activity'
          AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('student', ?, 'Suspicious rapid roommate decision activity', 'medium')
        ");
        $flag->bind_param("i", $user_id);
        $flag->execute();
    }
}

$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'The request has been rejected.'
];

header("Location: host_requests.php");
exit();
?>