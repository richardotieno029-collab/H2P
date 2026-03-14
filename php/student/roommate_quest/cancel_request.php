<?php
require_once "auth_student.php";
require_once "../../db_connect.php";

$student_id = $_SESSION['user_id'];
$host_id = intval($_GET['host_id']);

$stmt = $conn->prepare("
DELETE FROM roommate_requests
WHERE host_id=? AND student_id=?
");

$stmt->bind_param("ii",$host_id,$student_id);
$stmt->execute();

header("Location: view_hosts.php");
exit();
?>