<?php
session_start();
require_once "auth_student.php";
require_once "../../db_connect.php";
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

$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Listing deleted successfully.'
];

header("Location: roommate_quest.php");
exit();
?>