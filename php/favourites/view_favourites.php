<?php
require '../session.php';
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../student/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$query = "
SELECT rooms.*, houses.house_name
FROM favourites
JOIN rooms ON favourites.room_id = rooms.id
JOIN houses ON rooms.house_id = houses.house_id
WHERE favourites.student_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Favourites</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
<header>
<h2>My Favourite Rooms</h2>
</header>
<div class="houses-container">
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="house-card">
            <img src="../../<?= $row['image_path'] ?>" alt="Room">
            <h3><?= htmlspecialchars($row['house_name']) ?></h3>
            <p>Price: Ksh <?= $row['price'] ?></p>
            <p>Status: <?= $row['status'] ?></p>

            <a class="btn danger"
               href="remove_favourite.php?room_id=<?= $row['id'] ?>">
               Remove ❤️
            </a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="dashboard-actions">

        <a href="../student/browse_houses.php" class="dash-card">
            <h3>🔍 Browse Houses</h3>
            <p>No favourites yet. Browse houses to add</p>
        </a>
    <?php endif; ?>
</div>

</body>
</html>