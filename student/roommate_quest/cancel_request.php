<?php
require_once "auth_student.php";
require_once "../../includes/risk_engine.php";

$student_id = $_SESSION['user_id'];
$host_id = intval($_GET['host_id']);

$stmt = $conn->prepare("
DELETE FROM roommate_requests
WHERE host_id=? AND student_id=?
");

$stmt->bind_param("ii",$host_id,$student_id);
$stmt->execute();

// Log activity
$user_type = 'student';
$user_id   = $student_id;
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");

$action = 'CANCEL_ROOMMATE_REQUEST';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();

// Spam check - rapid join/cancel requests
$check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM activity_logs
    WHERE user_type='student'
      AND user_id=?
      AND action IN ('SEND_ROOMMATE_REQUEST','CANCEL_ROOMMATE_REQUEST')
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
          AND reason='Suspicious rapid roommate request activity'
          AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('student', ?, 'Suspicious rapid roommate request activity', 'medium')
        ");
        $flag->bind_param("i", $user_id);
        $flag->execute();
    }
}

header("Location: view_hosts.php");
exit();
?>