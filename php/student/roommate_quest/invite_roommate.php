<?php
require_once "auth_student.php";
require_once "../../db_connect.php";
include "../../toast.php";

$student_id = $_SESSION['user_id'];

/* fetch student info */

$stmt = $conn->prepare("
SELECT full_name,email,phone,profile_image
FROM students
WHERE id = ?
");

$stmt->bind_param("i",$student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();


/* HANDLE FORM SUBMISSION */

if($_SERVER["REQUEST_METHOD"] == "POST"){

$gender = $_POST['gender'];
$age = $_POST['age'];
$year = $_POST['year'];
$religion = $_POST['religion'];
$allergies = $_POST['allergies'];
$disability = $_POST['disability'];
$likes = $_POST['likes'];
$dislikes = $_POST['dislikes'];

$area = $_POST['area'];
$room_type = $_POST['room_type'];
$rent = $_POST['rent'];


/* ROOM PHOTO UPLOAD */

$room_photo = "";

if(!empty($_FILES['room_photo']['name'])){

$target_dir = "../../uploads/";
$filename = time() . "_" . basename($_FILES["room_photo"]["name"]);
$target_file = $target_dir . $filename;

move_uploaded_file($_FILES["room_photo"]["tmp_name"],$target_file);

$room_photo = $filename;

}


/* INSERT HOST LISTING */

$stmt = $conn->prepare("
INSERT INTO roommate_hosts
(student_id,name,gender,age,year_of_study,religion,allergies,disability,likes,dislikes,profile_photo,area,room_type,rent,room_photo)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
"ississsssssssis",
$student_id,
$student['full_name'],
$gender,
$age,
$year,
$religion,
$allergies,
$disability,
$likes,
$dislikes,
$student['profile_image'],
$area,
$room_type,
$rent,
$room_photo
);

$stmt->execute();

$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Listing successfully created.'
];

header("Location: roommate_quest.php");
exit();

}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Invite Roommate</title>

<link rel="stylesheet" href="../../styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="roommate_quest.php" class="back-btn">←</a>

<div class="rq-form">

<h2>Invite a Roommate</h2>

<form method="POST" enctype="multipart/form-data">

<h3>Your Details</h3>

<label>Gender</label>
<select name="gender" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
</select>

<label>Age</label>
<input type="number" name="age" required>

<label>Year of Study</label>
<input type="text" name="year" placeholder="Example: Year 2">

<label>Religion</label>
<input type="text" name="religion">

<label>Allergies</label>
<input type="text" name="allergies" placeholder="Leave blank if none">

<label>Disability</label>
<input type="text" name="disability" placeholder="Leave blank if none">

<label>Likes</label>
<textarea name="likes"></textarea>

<label>Dislikes</label>
<textarea name="dislikes"></textarea>


<h3>Room Details</h3>

<label>Area</label>
<input type="text" name="area" required>

<label>Room Type</label>
<select name="room_type">
<option>Single</option>
<option>Bedsitter</option>
<option>One Bedroom</option>
<option>Hostel</option>
</select>

<label>Rent Per Month (KES)</label>
<input type="number" name="rent" required>

<label>Room Photo</label>
<input type="file" name="room_photo">

<button type="submit" class="rq-submit">
Create Listing
</button>

</form>

</div>

</body>
</html>