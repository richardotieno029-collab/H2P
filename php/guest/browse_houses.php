<?php
require_once "../includes/config/db_connect.php";

$query = "
    SELECT 
        h.house_id,
        h.house_name,
        h.area,
        h.room_type,
        h.price,
        h.image_path,
        COUNT(r.id) AS total_rooms,
        SUM(r.status = 'vacant') AS vacant_rooms
    FROM houses h
    LEFT JOIN rooms r ON h.house_id = r.house_id
    WHERE h.status = 'active'
    GROUP BY h.house_id
    ORDER BY h.house_name ASC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Houses</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>
    <a href="../index/index.php" class="back-btn" title="Go back">←</a>
<h2> GUEST MODE </h2>
<header class="guest-header">
    <h2>H2P – Find a Place</h2>
    <a href="../student/signup_form.php" class="btn">Login / Sign up</a>
</header>

<div class="houses-container">

<?php while ($house = $result->fetch_assoc()): ?>

    <div class="house-card">
        <img src="<?= htmlspecialchars($house['image_path']) ?>" alt="House">

        <h3><?= htmlspecialchars($house['house_name']) ?></h3>

        <p><strong>Area:</strong> <?= htmlspecialchars($house['area']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($house['room_type']) ?></p>
        <p><strong>Price:</strong> KES <?= number_format($house['price']) ?></p>

        <?php if ($house['vacant_rooms'] > 0): ?>
            <span class="Bbadge badge-available">
                🟢 <?= $house['vacant_rooms'] ?> room(s) available
            </span>
        <?php else: ?>
            <span class="Bbadge badge-occupied">
                ❌ Fully occupied
            </span>
        <?php endif; ?>

        <a 
            href="view_rooms.php?house_id=<?= $house['house_id'] ?>" 
            class="btn">
            View Rooms
        </a>
    </div>

<?php endwhile;?>

</div>

<button id="scrollTopBtn" class="scrollTopBtn" title="Go to top">↑</button>
<script src="../includes/assets/js/scroll_top.js"></script>
<script>
initScrollTop();
</script>

</body>
</html>