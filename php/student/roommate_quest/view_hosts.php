<?php
require_once "auth_student.php";
require_once "../../db_connect.php";

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
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
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
<p><?= htmlspecialchars($host['year_of_study']); ?></p>

</div>

</div>

<div class="host-room">

<div class="house-card">
<?php if($host['room_photo']): ?>

<img src="../../uploads/<?= $host['room_photo']; ?>" class="room-photo">

<?php endif; ?>
</div>

<p><strong>Area:</strong> <?= htmlspecialchars($host['area']); ?></p>

<p><strong>Room Type:</strong> <?= htmlspecialchars($host['room_type']); ?></p>

<p><strong>Rent:</strong> KES <?= number_format($host['rent']); ?></p>

</div>

<div class="host-preferences">

<p><strong>Likes:</strong> <?= htmlspecialchars($host['likes']); ?></p>

<p><strong>Dislikes:</strong> <?= htmlspecialchars($host['dislikes']); ?></p>

</div>

<a href="join_host.php?host_id=<?= $host['host_id']; ?>" class="join-btn">
Join Host
</a>

</div>

<?php endwhile; ?>

<?php else: ?>

<p style="text-align:center;">No roommate listings available yet.</p>

<?php endif; ?>

</div>

</body>
</html>