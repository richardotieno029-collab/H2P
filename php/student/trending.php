<?php
require_once "auth_student.php";
require_once "../db_connect.php";

/* Mark notifications as read */
$clear = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$stmt = $conn->prepare($clear);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

/* --------------------------
   TRENDING QUERY
---------------------------*/

$sql = "
SELECT 
    h.*,
    l.full_name AS landlord_name,

    /* ROOMS */
    (SELECT COUNT(*) FROM rooms r WHERE r.house_id = h.house_id) AS total_rooms,

    (SELECT COUNT(*) 
     FROM rooms r 
     WHERE r.house_id = h.house_id 
     AND r.status = 'vacant') AS available_rooms,

    /* VIEWS */
    (SELECT COUNT(*) 
     FROM house_views v 
     WHERE v.house_id = h.house_id 
     AND v.viewed_at >= NOW() - INTERVAL 7 DAY) AS views,

    /* FAVOURITES */
    (SELECT COUNT(*) 
     FROM favourites f 
     WHERE f.house_id = h.house_id) AS favourites,

    /* TRENDING SCORE */
    (
        (SELECT COUNT(*) FROM house_views v 
         WHERE v.house_id = h.house_id 
         AND v.viewed_at >= NOW() - INTERVAL 7 DAY) * 2
        +
        (SELECT COUNT(*) FROM favourites f 
         WHERE f.house_id = h.house_id)
    ) AS trending_score,

    /* PENDING */
    CASE 
        WHEN EXISTS (
            SELECT 1
            FROM bookings b
            JOIN rooms r2 ON b.room_id = r2.id
            WHERE r2.house_id = h.house_id
            AND b.student_internal_id = ?
            AND b.status = 'pending'
        ) THEN 1 ELSE 0
    END AS has_pending

FROM houses h
JOIN landlords l ON h.landlord_id = l.id

WHERE h.status = 'active'

HAVING trending_score > 2

ORDER BY trending_score DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

/* Fetch student favourites */

$student_id = $_SESSION['user_id'];

$favHouses = [];

$stmtFav = $conn->prepare("
SELECT house_id
FROM favourites
WHERE student_internal_id = ?
");

$stmtFav->bind_param("i", $student_id);
$stmtFav->execute();
$resultFav = $stmtFav->get_result();

while ($row = $resultFav->fetch_assoc()) {
    $favHouses[] = $row['house_id'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending Houses</title>

<link rel="stylesheet" href="../styles.css">

</head>

<body>

<?php include "../toast.php"; ?>

<a href="dashboard.php" class="back-btn" title="Go back">
←
</a>

<h2>🔥 Trending This Week</h2>

<div class="houses-container">

<?php if ($result->num_rows > 0): ?>

<?php while ($house = $result->fetch_assoc()): ?>

<div class="house-card <?= $house['has_pending'] ? 'pending-house' : '' ?>">

<?php
$urgency = '';

if ($house['available_rooms'] > 0 && $house['available_rooms'] <= 2) {
    $urgency = 'urgent';
} elseif ($house['available_rooms'] > 2 && $house['available_rooms'] <= 5) {
    $urgency = 'limited';
}
?>

<?php if ($urgency): ?>
<span class="u-badge <?= $urgency ?>">
<?= ucfirst($urgency) ?>
</span>
<?php endif; ?>

<!-- TRENDING BADGE -->
<span class="trend-badge">
🔥 <?= $house['trending_score'] ?>
</span>

<a href="house_details.php?id=<?= $house['house_id']; ?>">

<div class="card">

<div class="image-wrapper">

<img src="<?= $house['image_path']; ?>" alt="House Image">

<div class="icon-overlay">

<?php if($house['electricity_available'] == 1): ?>
<i class="fas fa-bolt"></i>
<?php endif; ?>

<?php if($house['water_available'] == 1): ?>
<i class="fas fa-tint"></i>
<?php endif; ?>

<?php if($house['wifi_available'] == 1): ?>
<i class="fas fa-wifi"></i>
<?php endif; ?>

<?php if($house['hot_shower'] == 1): ?>
<i class="fas fa-shower"></i>
<?php endif; ?>

</div>
</div>
</div>

</a>

<div class="house-stats">
<span>👁 <?= $house['views'] ?></span>
<span>❤️ <?= $house['favourites'] ?></span>
</div>

<h3><?= htmlspecialchars($house['house_name']); ?></h3>

<p><strong>Area:</strong> <?= htmlspecialchars($house['area']); ?></p>

<p><strong>Room Type:</strong> <?= htmlspecialchars($house['room_type']); ?></p>

<p><strong>Price:</strong> From KES <?= number_format($house['price']); ?></p>

<p><strong>Landlord:</strong> <?= htmlspecialchars($house['landlord_name']); ?></p>

<p><strong>Description:</strong>
<?= nl2br(htmlspecialchars($house['description'])); ?>
</p>

<div class="availability">

<?php if ($house['available_rooms'] > 0): ?>

<span class="badge available">
🟢 <?= $house['available_rooms']; ?> room(s) available
</span>

<?php else: ?>

<span class="badge full">
🔴 Fully occupied
</span>

<?php endif; ?>

</div>

<a href="view_room.php?house_id=<?= $house['house_id']; ?>" class="btn">
View Rooms
</a>

<button
class="fav-btn <?= in_array($house['house_id'], $favHouses) ? 'active' : '' ?>"
data-house-id="<?= $house['house_id'] ?>">
<i class="fa fa-heart"></i>
</button>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>No trending houses yet.</p>

<?php endif; ?>

</div>

<script>

document.querySelectorAll('.fav-btn').forEach(button => {

button.addEventListener('click', function(e){

e.preventDefault();
e.stopPropagation();

const houseId = this.dataset.houseId;
const btn = this;

fetch('../favourites/toggle_favourite.php', {

method: 'POST',

headers: {
'Content-Type': 'application/x-www-form-urlencoded'
},

body: 'house_id=' + houseId

})

.then(res => res.json())

.then(data => {

if(data.status === 'added'){
btn.classList.add('active');
}else{
btn.classList.remove('active');
}

});

});

});

</script>

</body>
</html>