<?php
session_start();
require_once "auth_student.php";
include '../../includes/toast.php';

$student_id = $_SESSION['user_id'];

/* Get host listing */

$stmt = $conn->prepare("
SELECT *
FROM roommate_hosts
WHERE student_id = ? AND status='OPEN'
");

$stmt->bind_param("i",$student_id);
$stmt->execute();
$host = $stmt->get_result()->fetch_assoc();

if(!$host){
echo "<p style='text-align:center;'>You don't have an active roommate listing.</p>";
exit();
}

$host_id = $host['host_id'];

/* Cleanup stale requests older than 2 days */
$conn->query("UPDATE roommate_requests SET status='CANCELLED' WHERE status='PENDING' AND created_at < UTC_TIMESTAMP() - INTERVAL 2 DAY");

/* Fetch join requests */

$stmt = $conn->prepare("
SELECT *
FROM roommate_requests
WHERE host_id = ?
ORDER BY created_at DESC
");

$stmt->bind_param("i",$host_id);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roommate Requests</title>

<link rel="stylesheet" href="../../includes/assets/css/styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="roommate_quest.php" class="back-btn">←</a>

<h2 style="text-align:center;">Your Roommate Listing</h2>

<div class="houses-container">

<!-- HOST LISTING -->

<div class="rq-host-card">

<div class="host-header">

<img src="../../uploads/<?= $host['profile_photo'] ?>" class="host-photo">

<div>

<h3><?= htmlspecialchars($host['name']) ?></h3>

<p>Age: <?= $host['age'] ?></p>
<p>Level: <?= htmlspecialchars($host['year_of_study']) ?></p>

</div>

</div>

<div class="card">
<div class="image-wrapper">

<?php if($host['room_photo']): ?>

<img src="../../uploads/<?= $host['room_photo'] ?>" class="room-photo">

<?php endif; ?>

</div>
</div>

<p><strong>Area:</strong> <?= htmlspecialchars($host['area']) ?></p>
<p><strong>Room Type:</strong> <?= htmlspecialchars($host['room_type']) ?></p>
<p><strong>Rent:</strong> KES <?= number_format($host['rent']) ?></p>

<div class="host-preferences">

<p><strong>Likes:</strong> <?= htmlspecialchars($host['likes']) ?></p>
<p><strong>Dislikes:</strong> <?= htmlspecialchars($host['dislikes']) ?></p>

</div>

<div class="rq-actions">

<a href="edit_listing.php?host_id=<?= $host_id ?>" class="accept-btn">
Edit Listing
</a>

<a href="delete_listing.php?host_id=<?= $host_id ?>" 
class="reject-btn"
onclick="return confirm('Delete your roommate listing?')">
Delete Listing
</a>

</div>

</div>

</div>

<h2 style="text-align:center;">Roommate Requests</h2>

<div class="houses-container">

<?php if($result->num_rows > 0): ?>

<?php while($req = $result->fetch_assoc()): ?>

<div class="rq-host-card">

<div class="host-header">

<img src="../../uploads/<?= $req['profile_photo'] ?>" class="host-photo">

<div>

<h3><?= htmlspecialchars($req['name']) ?></h3>

<p>Age: <?= $req['age'] ?></p>

<p>Level: <?= htmlspecialchars($req['year_of_study']) ?></p>

</div>

</div>

<div class="host-preferences">

<p><strong>Religion:</strong> <?= htmlspecialchars($req['religion']) ?></p>

<p><strong>Allergies:</strong> <?= htmlspecialchars($req['allergies']) ?></p>

<p><strong>Disability:</strong> <?= htmlspecialchars($req['disability']) ?></p>

<p><strong>Likes:</strong> <?= htmlspecialchars($req['likes']) ?></p>

<p><strong>Dislikes:</strong> <?= htmlspecialchars($req['dislikes']) ?></p>

</div>

<div class="rq-actions">

<a href="approve_request.php?id=<?= $req['request_id'] ?>" 
class="accept-btn"
onclick="return confirm('Other requests will be auto rejected.')">
Accept
</a>

<a href="reject_request.php?id=<?= $req['request_id'] ?>" 
class="reject-btn">
Reject
</a>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<p style="text-align:center;">No requests yet.</p>

<?php endif; ?>

</div>
<script src="../assets/js/auto_refresh.js"></script>

<script>
startAutoRefresh(10); // refresh after 10 seconds of inactivity
</script>
</body>
</html>