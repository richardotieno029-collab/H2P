<?php
require_once "auth_student.php";

// Fetch unread notifications count
$notifStmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$notifStmt->bind_param("s", $_SESSION['user_id']);
$notifStmt->execute();
$notif = $notifStmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | H2P</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="../scripts.js"></script>

</head>

<?php include "dashboard_header.php"; ?>
<?php include "../toast.php"; ?>
<body>
<div class="dashboard-container">

    <h2 class="dashboard-title">
        Welcome back, <?= htmlspecialchars($_SESSION['user_name']); ?>
    </h2>

    <!-- ACTION CARDS -->
    <div class="dashboard-actions">

        <a href="browse_houses.php" class="dash-card">
            <h3>🔍 Browse All Houses</h3>
            <p>Explore all available houses and rooms</p>
            <?php if ($notif['total'] > 0): ?>
        <span class="notify-dot"></span>
    <?php endif; ?>
        </a>

        <a href="../favourites/view_favourites.php" class="dash-card">
            <h3>❤️ My Favourites</h3>
            <p>Rooms you’ve saved</p>
        </a>

        <a href="roommate_quest/roommate_quest.php" class="dash-card">
            <h3>🆕 Room mate Quest</h3>
            <p>Find the perfect roommate</p>
        </a>

        <a href="trending.php" class="dash-card">
            <h3>🔥 Trending Houses</h3>
            <p>View weekly trending houses</p>
        </a>

    </div>
<h2>Discover Houses</h2>
<h3>👀 Recently Viewed</h3>

<div class="houses-container">

<?php
$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT h.*
FROM houses h
JOIN house_views v ON h.house_id = v.house_id
WHERE v.student_id=?
  AND h.status = 'active'
ORDER BY v.viewed_at DESC
LIMIT 3
");

$stmt->bind_param("i",$student_id);
$stmt->execute();
$result = $stmt->get_result();

while($house = $result->fetch_assoc()):
?>

<div class="house-card">
<a href="house_details.php?id=<?= $house['house_id'] ?>">
<img src="<?= $house['image_path'] ?>" width="100%">
</a>
<h4><?= htmlspecialchars($house['house_name']) ?></h4>
<p><?= htmlspecialchars($house['area']) ?></p>
<p>KES <?= number_format($house['price']) ?></p>
            <a href="view_room.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
                View Rooms
            </a>
</div>

<?php endwhile; ?>
<a href="recently_viewed.php">See more →</a>
</div>

</br>

<!-- MOST VIEWED -->
<h3>🔥 Most Viewed Houses</h3>
<div class="houses-container">

<?php
$query = "
SELECT h.*, COUNT(v.id) AS views
FROM houses h
LEFT JOIN house_views v ON h.house_id = v.house_id
WHERE h.status = 'active'
GROUP BY h.house_id
ORDER BY views DESC
LIMIT 3
";

$result = $conn->query($query);

while($house = $result->fetch_assoc()):
?>

<div class="house-card">
<a href="house_details.php?id=<?= $house['house_id'] ?>">
<img src="<?= $house['image_path'] ?>" width="100%">
</a>
 <span>👁 <?= $house['views'] ?></span>
<h4><?= htmlspecialchars($house['house_name']) ?></h4>
<p><?= htmlspecialchars($house['area']) ?></p>
<p>KES <?= number_format($house['price']) ?></p>

            <a href="view_room.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
                View Rooms
            </a>

</div>

<?php endwhile; ?>
<a href="most_viewed.php">See more →</a>
</div>

</br>
<h3>⭐ Most Favourited Houses</h3>

<div class="houses-container">

<?php
$query = "
SELECT h.*, COUNT(f.fav_id) AS fav_count
FROM houses h
LEFT JOIN favourites f ON h.house_id = f.house_id
WHERE h.status = 'active'
GROUP BY h.house_id
ORDER BY fav_count DESC
LIMIT 3
";

$result = $conn->query($query);

while($house = $result->fetch_assoc()):
?>

<div class="house-card">
<a href="house_details.php?id=<?= $house['house_id'] ?>">
<img src="<?= $house['image_path'] ?>" width="100%">
</a>
<span>❤️ <?= $house['fav_count'] ?></span>
<h4><?= htmlspecialchars($house['house_name']) ?></h4>
<p><?= htmlspecialchars($house['area']) ?></p>
<p>KES <?= number_format($house['price']) ?></p>
            <a href="view_room.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
                View Rooms
            </a>
</div>

<?php endwhile; ?>
<a href="most_favourited.php">See more →</a>
</div>

</br>


<!-- RECENTLY ADDED -->
<h3> Recently Added houses</h3>
<div class="houses-container">

<?php
$query = "
    SELECT h.house_id, h.*
    FROM houses h
    WHERE h.status = 'active'
    ORDER BY h.created_at DESC
    LIMIT 3
";
$result = $conn->query($query);

while($house = $result->fetch_assoc()):
?>

<div class="house-card">
<a href="house_details.php?id=<?= $house['house_id'] ?>">
<img src="<?= $house['image_path'] ?>" width="100%">
</a>
<h4><?= htmlspecialchars($house['house_name']) ?></h4>
<p><?= htmlspecialchars($house['area']) ?></p>
<p>KES <?= number_format($house['price']) ?></p>

            <a href="view_room.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
                View Rooms
            </a>

</div>

<?php endwhile; ?>
<a href="recently_added.php">See more →</a>
</div>

</br>



</div>
<script src="../assets/js/auto_refresh.js"></script>

<script>
startAutoRefresh(10); // refresh after 10 seconds of inactivity
</script>
</body>
</html>
