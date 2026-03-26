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
    <!-- recently added -->
<h3> Recently Added Houses</h3>
<div class="houses-container">

<?php
$query = "
SELECT h.*
FROM houses h
WHERE h.status = 'active'
ORDER BY h.created_at DESC
LIMIT 10
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
</div>
</body>
</html>