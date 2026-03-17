<?php
require_once "auth_student.php";
require_once "../../db_connect.php";
include "../../toast.php";


$student_id = $_SESSION['user_id'];

/* Auto-cleanup old roommate data */
// Cancel pending requests older than 2 days
$conn->query("UPDATE roommate_requests SET status='CANCELLED' WHERE status='PENDING' AND created_at < NOW() - INTERVAL 2 DAY");

// Close listings older than 2 weeks
$conn->query("UPDATE roommate_hosts SET status='CLOSED' WHERE status='OPEN' AND created_at < NOW() - INTERVAL 2 WEEK");

// Remove matches older than 1 week (my roommate access expires)
$conn->query("DELETE FROM roommate_matches WHERE created_at < NOW() - INTERVAL 1 WEEK");

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

/* Check if student already has an open roommate listing */

$checkHost = $conn->prepare("
SELECT host_id
FROM roommate_hosts
WHERE student_id = ?
AND status = 'OPEN'
LIMIT 1
");

$checkHost->bind_param("i",$student_id);
$checkHost->execute();

$hostResult = $checkHost->get_result();

$hasHost = $hostResult->num_rows > 0;

//check for match
$matchCheck = $conn->prepare("
SELECT match_id
FROM roommate_matches
WHERE host_id=? OR guest_id=?
");

$matchCheck->bind_param("ii",$student_id, $student_id);
$matchCheck->execute();

$hasMatch = $matchCheck->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

<div class="rq-options">

<?php if($hasHost): ?>

<a href="host_requests.php" class="rq-btn invite">
View Requests
</a>

<?php else: ?>

<a href="invite_roommate.php" class="rq-btn invite">
Invite a Roommate
</a>

<?php endif; ?>

<a href="view_hosts.php" class="rq-btn join">
Join a Roommate
</a>

</div>

<?php if($hasMatch): ?>
<a href="my_roommate.php" class="rq-btn join">
My Roommate 🎉
</a>
<?php endif; ?>

</div>

</body>
</html>