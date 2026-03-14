<?php

require_once "auth_student.php";
require_once "../../db_connect.php";

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

$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Roommate matched. You can contact each other.'
];
header("Location: roommate_quest.php");
exit();

?>