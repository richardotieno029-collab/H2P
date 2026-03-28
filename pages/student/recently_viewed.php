<?php
require_once "auth_student.php";

$student_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recently Viewed | H2P</title>

<link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>

<body>

<a href="dashboard.php" class="back-btn">←</a>

<?php include "../includes/toast.php"; ?>

<div class="dashboard-container">

<!-- =========================
     APPROVED ROOM (TOP)
========================= -->
<?php
$stmt = $conn->prepare("
SELECT 
    r.id AS room_id,
    r.room_number,
    r.image_path AS room_image,
    h.house_name,
    l.phone,
    l.email
FROM bookings b
JOIN rooms r ON b.room_id = r.id
JOIN houses h ON r.house_id = h.house_id
JOIN landlords l ON h.landlord_id = l.id
WHERE b.student_internal_id = ?
AND b.status = 'approved'
LIMIT 1
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$approved = $stmt->get_result()->fetch_assoc();
?>

<?php if($approved): ?>

<h3>🏠 Your Approved Room</h3>

<div class="houses-container">

<div class="house-card approved-room">

<span class="badge approved">✅ Approved</span>

<img src="<?= $approved['room_image'] ?>" class="room-photo">

<h3>Room <?= htmlspecialchars($approved['room_number']) ?></h3>
<p><?= htmlspecialchars($approved['house_name']) ?></p>

<a href="contact_landlord.php?room_id=<?= $approved['room_id'] ?>" class="btn contact-btn">
📞 Contact Landlord
</a>

</div>

</div>

<?php endif; ?>


<!-- =========================
     RECENTLY VIEWED
========================= -->
<h3>👀 Recently Viewed</h3>

<div class="houses-container">

<?php
$stmt = $conn->prepare("
SELECT DISTINCT h.*
FROM houses h
JOIN house_views v ON h.house_id = v.house_id
WHERE v.student_id = ?
AND h.status = 'active'
ORDER BY v.viewed_at DESC
LIMIT 10
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0):
while($house = $result->fetch_assoc()):
?>

<div class="house-card">

<a href="house_details.php?id=<?= $house['house_id'] ?>">
<img src="<?= $house['image_path'] ?>" width="100%">
</a>

<h4><?= htmlspecialchars($house['house_name']) ?></h4>
<p><?= htmlspecialchars($house['area']) ?></p>
<p>KES <?= number_format($house['price']) ?></p>

<a href="view_room.php?house_id=<?= $house['house_id']; ?>" class="btn">
View Rooms
</a>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>No recently viewed houses yet.</p>

<?php endif; ?>

</div>

</div>

</body>
</html>