<?php
require_once "auth_student.php";
require_once "../../db_connect.php";

$student_id = $_SESSION['user_id'];

/* Fetch student basic info */

$stmt = $conn->prepare("
SELECT full_name, email, phone, profile_image
FROM students
WHERE id = ?
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Roommate Quest</title>

<link rel="stylesheet" href="../../styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="../dashboard.php" class="back-btn">←</a>

<div class="rq-container">

<h2>Roommate Quest</h2>

<p class="intro">
Find the perfect roommate or invite someone to share your space.
</p>

<div class="student-card">

<img src="../../uploads/<?= $student['profile_image']; ?>" class="student-photo">

<div>

<h3><?= htmlspecialchars($student['full_name']); ?></h3>

<p>Email:<?= htmlspecialchars($student['email']); ?></p>

<p>Phone Number:<?= htmlspecialchars($student['phone']); ?></p>

</div>

</div>

<div class="rq-options">

<a href="invite_roommate.php" class="rq-btn invite">
Invite a Roommate
</a>

<a href="view_hosts.php" class="rq-btn join">
Join a Roommate
</a>

</div>

</div>

</body>
</html>