<?php
require_once "../includes/config/db_connect.php";

if (!isset($_GET['house_id']) || !is_numeric($_GET['house_id'])) {
    die("Invalid house selected.");
}

$house_id = (int) $_GET['house_id'];

/* House name */
$houseStmt = $conn->prepare(
    "SELECT house_name FROM houses WHERE house_id = ? AND status = 'active'"
);
$houseStmt->bind_param("i", $house_id);
$houseStmt->execute();
$house = $houseStmt->get_result()->fetch_assoc();

/* Rooms */
$roomStmt = $conn->prepare(
    "SELECT room_number, image_path, status 
     FROM rooms 
     WHERE house_id = ?
     ORDER BY room_number ASC"
);
$roomStmt->bind_param("i", $house_id);
$roomStmt->execute();
$rooms = $roomStmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms in <?= htmlspecialchars($house['house_name']) ?></title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>

<header class="guest-header">
    <a href="browse_houses.php" class="back-btn">← </a>
    <a href="../student/login_form.php" class="btn">Login to Book</a>
</header>

<h2>Rooms in <?= htmlspecialchars($house['house_name']) ?></h2>

<div class="houses-container">

<?php while ($room = $rooms->fetch_assoc()): ?>

    <div class="house-card">
        <img src="<?= htmlspecialchars($room['image_path']) ?>" alt="Room">

        <h3>Room <?= htmlspecialchars($room['room_number']) ?></h3>

        <?php if ($room['status'] === 'vacant'): ?>
            <span class="Bbadge badge-available">🟢 Available</span>
        <?php elseif ($room['status'] === 'occupied'): ?>
            <span class="Bbadge badge-occupied">❌ Occupied</span>
        <?php else: ?>
            <span class="Bbadge badge-selected">🟡 Selected</span>
        <?php endif; ?>

        <p class="muted">
            Login to book or contact landlord
        </p>
    </div>

<?php endwhile; ?>

</div>

<button id="scrollTopBtn" class="scrollTopBtn" title="Go to top">↑</button>
<script src="../includes/assets/js/scroll_top.js"></script>
<script>
initScrollTop();
</script>


</body>
</html>