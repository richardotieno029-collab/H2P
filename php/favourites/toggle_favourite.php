<?php
require_once "../student/auth_student.php";

$student_id = $_SESSION['user_id'];
$house_id = intval($_POST['house_id']);

$check = $conn->prepare("
SELECT fav_id 
FROM favourites
WHERE student_internal_id=? AND house_id=?
");

$check->bind_param("ii", $student_id, $house_id);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){

$del = $conn->prepare("
DELETE FROM favourites
WHERE student_internal_id=? AND house_id=?
");

$del->bind_param("ii", $student_id, $house_id);
$del->execute();

echo json_encode(["status"=>"removed"]);

}else{

$add = $conn->prepare("
INSERT INTO favourites (student_internal_id, house_id)
VALUES (?,?)
");

$add->bind_param("ii", $student_id, $house_id);
$add->execute();

echo json_encode(["status"=>"added"]);

}