<?php
require_once "auth_student.php";
require_once "../db_connect.php";

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
    <!-- Recently VIEWED -->
<h3>👀 Recently Viewed</h3>
<div class="houses-container">

<?php
$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT h.*
FROM houses h
JOIN house_views v ON h.house_id = v.house_id
WHERE v.student_id=?
ORDER BY v.viewed_at DESC
LIMIT 10
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
</div>
</body>
</html>