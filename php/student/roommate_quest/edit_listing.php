<?php
session_start();
require_once "auth_student.php";
require_once "../../db_connect.php";
include "../../toast.php";
include '../../toast.php';

$student_id = $_SESSION['user_id'];

/* Fetch current listing */

$stmt = $conn->prepare("
SELECT *
FROM roommate_hosts
WHERE student_id=? AND status='OPEN'
");

$stmt->bind_param("i",$student_id);
$stmt->execute();
$host = $stmt->get_result()->fetch_assoc();

if(!$host){
echo "No listing found.";
exit();
}

/* Update Listing */

if($_SERVER['REQUEST_METHOD']=="POST"){

$name = $_POST['name'];
$gender = $_POST['gender'];
$age = $_POST['age'];
$year = $_POST['year_of_study'];
$religion = $_POST['religion'];
$allergies = $_POST['allergies'];
$disability = $_POST['disability'];
$likes = $_POST['likes'];
$dislikes = $_POST['dislikes'];

$area = $_POST['area'];
$room_type = $_POST['room_type'];
$rent = $_POST['rent'];

$stmt = $conn->prepare("
UPDATE roommate_hosts
SET name=?, gender=?, age=?, year_of_study=?, religion=?, allergies=?, disability=?, likes=?, dislikes=?, area=?, room_type=?, rent=?
WHERE host_id=? AND student_id=?
");

$stmt->bind_param(
"ssissssssssiii",
$name,
$gender,
$age,
$year,
$religion,
$allergies,
$disability,
$likes,
$dislikes,
$area,
$room_type,
$rent,
$host['host_id'],
$student_id
);

$stmt->execute();

$_SESSION['toast']=[
"type"=>"success",
"message"=>"Listing updated successfully"
];
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Listing updated successfully.'
];
header("Location: host_requests.php");
exit();

}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>

<link rel="stylesheet" href="../../styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="host_requests.php" class="back-btn">←</a>

<h2 style="text-align:center;">Edit Roommate Listing</h2>

<form method="POST" class="rq-form">

<label>Name</label>
<input type="text" name="name"
value="<?= htmlspecialchars($host['name']) ?>" required>


<label>Gender</label>
<select name="gender" required>

<option value="Male" <?= $host['gender']=="Male"?"selected":"" ?>>Male</option>
<option value="Female" <?= $host['gender']=="Female"?"selected":"" ?>>Female</option>
<option value="Other" <?= $host['gender']=="Other"?"selected":"" ?>>Other</option>

</select>


<label>Age</label>
<input type="number" name="age"
value="<?= $host['age'] ?>" required>


<label>Year of Study</label>
<select name="year_of_study">

<option value="1" <?= $host['year_of_study']=="1"?"selected":"" ?>>1</option>
<option value="2" <?= $host['year_of_study']=="2"?"selected":"" ?>>2</option>
<option value="3" <?= $host['year_of_study']=="3"?"selected":"" ?>>3</option>
<option value="4" <?= $host['year_of_study']=="4"?"selected":"" ?>>4</option>

</select>


<label>Religion</label>
<input type="text" name="religion"
value="<?= htmlspecialchars($host['religion']) ?>">


<label>Allergies</label>
<input type="text" name="allergies"
value="<?= htmlspecialchars($host['allergies']) ?>"
placeholder="e.g Dust, pets, strong perfumes">


<label>Disability</label>
<input type="text" name="disability"
value="<?= htmlspecialchars($host['disability']) ?>"
placeholder="e.g Wheelchair access, hearing aid">


<label>Likes</label>
<textarea name="likes"
placeholder="e.g Quiet environment, studying, gaming"><?= htmlspecialchars($host['likes']) ?></textarea>


<label>Dislikes</label>
<textarea name="dislikes"
placeholder="e.g Loud music, smoking, late night parties"><?= htmlspecialchars($host['dislikes']) ?></textarea>


<hr>

<h3>Room Details</h3>


<label>Area</label>
<select name="area">

<option <?= $host['area']=="Bagik"?"selected":"" ?>>Bagik</option>
<option <?= $host['area']=="Kayole"?"selected":"" ?>>Kayole</option>
<option <?= $host['area']=="Spring Valley"?"selected":"" ?>>Spring Valley</option>
<option <?= $host['area']=="Kangaru"?"selected":"" ?>>Kangaru</option>
<option <?= $host['area']=="Kamiu"?"selected":"" ?>>Kamiu</option>
<option <?= $host['area']=="Gakwegori"?"selected":"" ?>>Gakwegori</option>
<option <?= $host['area']=="Leaders"?"selected":"" ?>>Leaders</option>
<option <?= $host['area']=="Town"?"selected":"" ?>>Town</option>
<option <?= $host['area']=="Perez"?"selected":"" ?>>Perez</option>
<option <?= $host['area']=="Njukiri"?"selected":"" ?>>Njukiri</option>
<option <?= $host['area']=="Others"?"selected":"" ?>>Others</option>

</select>


<label>Room Type</label>
<select name="room_type">

<option <?= $host['room_type']=="Single"?"selected":"" ?>>Single</option>
<option <?= $host['room_type']=="Bedsitter"?"selected":"" ?>>Bedsitter</option>
<option <?= $host['room_type']=="One Bedroom"?"selected":"" ?>>One Bedroom</option>
<option <?= $host['room_type']=="Two Bedroom"?"selected":"" ?>>Two Bedroom</option>
<option <?= $host['room_type']=="Hostel"?"selected":"" ?>>Hostel</option>

</select>


<label>Monthly Rent</label>
<input type="number" name="rent"
value="<?= $host['rent'] ?>" required>


<br><br>

<button type="submit" class="rq-btn invite">
Update Listing
</button>

</form>

</body>
</html>