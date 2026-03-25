<?php
require_once "auth_student.php";

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
<a href="dashboard.php" class="back-btn" title="Go back">
    ←
</a>

<?php include "../toast.php"; ?>
<body>
<div class="dashboard-container">
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
LIMIT 10
";

$result = $conn->query($query);

while($house = $result->fetch_assoc()):
?>

<div class="house-card">
<a href="house_details.php?id=<?= $house['house_id'] ?>">
<img src="<?= $house['image_path'] ?>" width="100%">
</a>
<div class="house-stats">
    <span>👁 <?= $house['views'] ?></span>
</div>
<h4><?= htmlspecialchars($house['house_name']) ?></h4>
<p><?= htmlspecialchars($house['area']) ?></p>
<p>KES <?= number_format($house['price']) ?></p>

            <a href="view_room.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
                View Rooms
            </a>

</div>

<?php endwhile; ?>
</div>
</body>
</html>