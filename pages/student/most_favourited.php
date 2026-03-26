<?php
require_once "auth_student.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | H2P</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
   

</head>
<a href="dashboard.php" class="back-btn" title="Go back">
    ←
</a>

<?php include "../includes/toast.php"; ?>
<body>
<div class="dashboard-container">
    <!-- MOST VIEWED -->
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
LIMIT 10
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
</div>
</body>
</html>