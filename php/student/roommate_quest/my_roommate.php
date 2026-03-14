<?php
require_once "auth_student.php";
require_once "../../db_connect.php";

$student_id = $_SESSION['user_id'];

/* Check if student has a roommate match */

$stmt = $conn->prepare("
SELECT *
FROM roommate_matches
WHERE host_id = ? OR guest_id = ?
LIMIT 1
");

$stmt->bind_param("ii",$student_id,$student_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
echo "<p style='text-align:center;'>No roommate match yet.</p>";
exit();
}

$match = $result->fetch_assoc();

/* Determine the other student's ID */

if($match['host_id'] == $student_id){

$other_id = $match['guest_id'];
$role = "Host";

}else{

$other_id = $match['host_id'];
$role = "Guest";

}

/* Fetch the other student's details */

$stmt = $conn->prepare("
SELECT full_name,email,phone,profile_image
FROM students
WHERE id = ?
");

$stmt->bind_param("i",$other_id);
$stmt->execute();

$roommate = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Roommate</title>

<link rel="stylesheet" href="../../styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="roommate_quest.php" class="back-btn">←</a>

<div class="rq-form">

<h2>Your Roommate 🎉</h2>

<p style="text-align:center;">
Match confirmed — you can now contact each other.
</p>

<div class="host-header">

<img src="../../uploads/<?= $roommate['profile_image']; ?>" class="host-photo">

<div>

<h3><?= htmlspecialchars($roommate['full_name']); ?></h3>

<p>📧 <?= htmlspecialchars($roommate['email']); ?></p>

<p>📞 <?= htmlspecialchars($roommate['phone']); ?></p>

</div>

</div>

<div style="margin-top:20px; text-align:center;">

<span class="badge available">
You matched successfully!
</span>

</div>

</div>

</body>
</html>