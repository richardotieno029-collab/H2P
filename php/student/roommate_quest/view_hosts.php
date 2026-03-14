<?php
require_once "auth_student.php";
require_once "../../db_connect.php";
include "../../toast.php";

$student_id = $_SESSION['user_id'];

/* Fetch all open hosts except the current student */

$stmt = $conn->prepare("
SELECT *
FROM roommate_hosts
WHERE status = 'OPEN'
AND student_id != ?
ORDER BY created_at DESC
");

$stmt->bind_param("i",$student_id);
$stmt->execute();
$result = $stmt->get_result();

/* Fetch hosts the student already requested */

$sentRequests = [];

$req = $conn->prepare("
SELECT host_id
FROM roommate_requests
WHERE student_id = ?
");

$req->bind_param("i",$student_id);
$req->execute();

$resReq = $req->get_result();

while($row = $resReq->fetch_assoc()){
    $sentRequests[] = $row['host_id'];
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join a Roommate</title>

<link rel="stylesheet" href="../../styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="roommate_quest.php" class="back-btn">←</a>

<h2 style="text-align:center;">Available Roommate Hosts</h2>

<div class="houses-container">

<?php if($result->num_rows > 0): ?>

<?php while($host = $result->fetch_assoc()): ?>

<div class="rq-host-card">

<div class="host-header">

<img src="../../uploads/<?= $host['profile_photo']; ?>" class="host-photo">

<div>

<h3><?= htmlspecialchars($host['name']); ?></h3>

<p>Age: <?= $host['age']; ?></p>
<p>Level: <?= htmlspecialchars($host['year_of_study']); ?></p>

</div>

</div>

<div>

<div class="card">
<div class="image-wrapper">

<?php if($host['room_photo']): ?>

<img src="../../uploads/<?= $host['room_photo']; ?>" class="room-photo">

<?php endif; ?>

</div>
</div>

<p><strong>Area:</strong> <?= htmlspecialchars($host['area']); ?></p>

<p><strong>Room Type:</strong> <?= htmlspecialchars($host['room_type']); ?></p>

<p><strong>Rent:</strong> KES <?= number_format($host['rent']); ?></p>

</div>

<div class="host-preferences">

<p><strong>Likes:</strong> <?= htmlspecialchars($host['likes']); ?></p>

<p><strong>Dislikes:</strong> <?= htmlspecialchars($host['dislikes']); ?></p>

</div>

<?php if(in_array($host['host_id'],$sentRequests)): ?>

<button  disabled>
Request Sent
</button>

<a href="cancel_request.php?host_id=<?= $host['host_id']; ?>" 
class="danger-btn" onclick="return confirm('Cancel roommate request?')">
Cancel Request
</a>

<?php else: ?>

<a href="join_host.php?host_id=<?= $host['host_id']; ?>" 
class="join-btn">
Join Host
</a>

<?php endif; ?>

</div>

<?php endwhile; ?>

<?php else: ?>

<p style="text-align:center;">No roommate listings available yet.</p>

<?php endif; ?>

</div>

</body>
</html>