<?php
require_once "auth_student.php";
require_once "../../db_connect.php";
include "../../toast.php";

$student_id = $_SESSION['user_id'];

$host_id = intval($_GET['host_id']);

/* Get host details */

$stmt = $conn->prepare("
SELECT h.*, s.phone, s.email
FROM roommate_hosts h
JOIN students s ON h.student_id = s.id
WHERE h.host_id = ?
");

$stmt->bind_param("i",$host_id);
$stmt->execute();

$host = $stmt->get_result()->fetch_assoc();


/* HANDLE JOIN REQUEST */

if($_SERVER['REQUEST_METHOD'] == "POST"){

$gender = $_POST['gender'];
$age = $_POST['age'];
$year = $_POST['year'];
$religion = $_POST['religion'];
$allergies = $_POST['allergies'];
$disability = $_POST['disability'];
$likes = $_POST['likes'];
$dislikes = $_POST['dislikes'];


/* check if already requested */

$check = $conn->prepare("
SELECT request_id 
FROM roommate_requests
WHERE host_id=? AND student_id=?
");

$check->bind_param("ii",$host_id,$student_id);
$check->execute();

if($check->get_result()->num_rows > 0){

echo "<p style='color:red;text-align:center;'>You already sent a request.</p>";

}else{

$stmt = $conn->prepare("
INSERT INTO roommate_requests
(host_id,student_id,name,gender,age,year_of_study,religion,allergies,disability,likes,dislikes,profile_photo)
SELECT ?, id, full_name, ?, ?, ?, ?, ?, ?, ?, ?, profile_image
FROM students
WHERE id = ?
");

$stmt->bind_param(
"isssssssss",
$host_id,
$gender,
$age,
$year,
$religion,
$allergies,
$disability,
$likes,
$dislikes,
$student_id
);

$stmt->execute();

$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'Request sent. Pleae wait for the host to reply.'
];
header("Location: view_hosts.php");
exit();

}

}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Host</title>

<link rel="stylesheet" href="../../styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="view_hosts.php" class="back-btn">←</a>

<div class="rq-form">

<h2>Join <?= htmlspecialchars($host['name']) ?></h2>

<p><strong>Area:</strong> <?= htmlspecialchars($host['area']) ?></p>
<p><strong>Room:</strong> <?= htmlspecialchars($host['room_type']) ?></p>
<p><strong>Rent:</strong> KES <?= number_format($host['rent']) ?></p>

<form method="POST">

<h3>Your Details</h3>

<label>Gender</label>
<select name="gender" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>

<label>Age</label>
<input type="number" name="age" required>

<label>Year of Study</label>
<select name="year" required>
<option value="">Select</option>
<option>Year 1</option>
<option>Year 2</option>
<option>Year 3</option>
<option>Year 4</option>
<option>Graduated</option>
<option>Other</option>
</select>

<label>Religion</label>
<select name="religion" required>
<option value="">Select</option>
    <option>Christian</option>
    <option>Muslim</option>
    <option>Hindi</option>
    <option>Budhist</option>
    <option>Other</option>
</select>

<label>Allergies(if any)</label>
<input type="text" name="allergies" placeholder="e.g milk, perfumes, pollen...">

<label>Disability(if any)</label>
<input type="text" name="disability" placeholder="e.g short sighted, physically handicapped...">

<label>Likes(Optional)</label>
<textarea name="likes" placeholder="List your likes...e.g gaming, dancing"></textarea>

<label>Dislikes(Optional)</label>
<textarea name="dislikes" placeholder="List your dislikes...e.g drugs, loud music"></textarea>


<button class="rq-submit">
Send Join Request
</button>

</form>

</div>

</body>
</html>