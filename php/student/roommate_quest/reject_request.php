<?php
session_start();
require_once "auth_student.php";
require_once "../../db_connect.php";
include "../../toast.php";

$request_id = intval($_GET['id']);

/* remove request */

$stmt = $conn->prepare("
DELETE FROM roommate_requests
WHERE request_id=?
");

$stmt->bind_param("i",$request_id);
$stmt->execute();

$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'The request has been rejected.'
];

header("Location: host_requests.php");
exit();
?>