<?php

require_once "auth_student.php";
require_once "../../db_connect.php";
require_once "../../includes/risk_engine.php";

$student_id = $_SESSION['user_id'];

$request_id = intval($_GET['id']);

/* Get host + student info */

$stmt = $conn->prepare("
SELECT host_id
FROM roommate_requests
WHERE request_id = ?
");

$stmt->bind_param("i",$request_id);
$stmt->execute();

$result = $stmt->get_result();
$request = $result->fetch_assoc();

if(!$request){
die("Request not found");
}

$host_id = $request['host_id'];

/* Verify current user owns the host listing */

$stmt = $conn->prepare("
SELECT host_id
FROM roommate_hosts
WHERE host_id = ?
AND student_id = ?
");

$stmt->bind_param("ii",$host_id,$student_id);
$stmt->execute();

if($stmt->get_result()->num_rows == 0){
die("Unauthorized action");
}

/* Approve selected request */

$stmt = $conn->prepare("
UPDATE roommate_requests
SET status='APPROVED'
WHERE request_id=?
");

$stmt->bind_param("i",$request_id);
$stmt->execute();


/* Reject all other requests */

$stmt = $conn->prepare("
UPDATE roommate_requests
SET status='REJECTED'
WHERE host_id=? AND request_id != ?
");

$stmt->bind_param("ii",$host_id,$request_id);
$stmt->execute();

/* Get guest student id */

$stmt = $conn->prepare("
SELECT student_id
FROM roommate_requests
WHERE request_id = ?
");

$stmt->bind_param("i",$request_id);
$stmt->execute();

$guest = $stmt->get_result()->fetch_assoc();
$guest_id = $guest['student_id'];


/* Get host student id */

$stmt = $conn->prepare("
SELECT student_id
FROM roommate_hosts
WHERE host_id = ?
");

$stmt->bind_param("i",$host_id);
$stmt->execute();

$host = $stmt->get_result()->fetch_assoc();
$host_student_id = $host['student_id'];

//delete old matches from roommate_matches for this host
$stmt = $conn->prepare("
DELETE FROM roommate_matches
WHERE host_id=? OR guest_id=?
");

$stmt->bind_param("ii",$host_student_id,$guest_id);
$stmt->execute();


/* Insert match */

$stmt = $conn->prepare("
INSERT INTO roommate_matches (host_id, guest_id)
VALUES (?,?)
");

$stmt->bind_param("ii",$host_student_id,$guest_id);
$stmt->execute();

/* Close listing */

$stmt = $conn->prepare("
UPDATE roommate_hosts
SET status='CLOSED'
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

$action = 'APPROVE_ROOMMATE_REQUEST';
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
    'type' => 'success',
    'message' => 'Roommate matched. You can contact each other.'
];
header("Location: roommate_quest.php");
exit();

?>