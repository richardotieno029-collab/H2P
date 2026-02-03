<?php
session_start();
require_once "../db_connect.php";

/* --------------------------
   FILTER LOGIC
---------------------------*/
$sql = "
SELECT 
    h.house_id,
    h.house_name,
    h.area,
    h.room_type,
    h.price,
    h.description,
    h.image_path,
    l.full_name AS landlord_name,
    COUNT(r.id) AS total_rooms,
    SUM(r.status = 'vacant') AS available_rooms
FROM houses h
JOIN landlords l ON h.landlord_id = l.landlord_id
LEFT JOIN rooms r ON h.house_id = r.house_id
GROUP BY h.house_id
";


$params = [];
$types  = "";

// Area filter
if (!empty($_GET['area'])) {
    $sql .= " AND h.area = ?";
    $params[] = $_GET['area'];
    $types .= "s";
}

// Room type filter
if (!empty($_GET['room_type'])) {
    $sql .= " AND h.room_type = ?";
    $params[] = $_GET['room_type'];
    $types .= "s";
}

// Price filters
if (!empty($_GET['min_price'])) {
    $sql .= " AND h.price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "i";
}

if (!empty($_GET['max_price'])) {
    $sql .= " AND h.price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "i";
}

// Only available houses
if (!empty($_GET['vacant'])) {
    $sql .= " HAVING available_rooms > 0";
}

$sql .= " ORDER BY h.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Houses</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<?php include "../dashboard_header.php"; ?>
<a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
<h2>Browse Houses</h2>

<!-- --------------------------
     FILTERS
--------------------------- -->
<form method="GET" class="filters">
    <input type="text" name="area" placeholder="Area"
           value="<?php echo $_GET['area'] ?? ''; ?>">

    <select name="room_type">
        <option value="">All Room Types</option>
        <option value="Single">Single</option>
        <option value="Bedsitter">Bedsitter</option>
        <option value="One Bedroom">1 Bedroom</option>
    </select>

    <input type="number" name="min_price" placeholder="Min Price">
    <input type="number" name="max_price" placeholder="Max Price">

    <label>
        <input type="checkbox" name="vacant" value="1"
            <?php if (!empty($_GET['vacant'])) echo "checked"; ?>>
        Only Available
    </label>

    <button type="submit" class="btn">Apply Filters</button>
</form>

<!-- --------------------------
     HOUSES LIST
--------------------------- -->
<div class="houses-container">

<?php if ($result->num_rows > 0): ?>
    <?php while ($house = $result->fetch_assoc()): ?>
        <div class="house-card">

        <?php
$urgency = '';
$label = '';

if ($house['available_rooms'] > 0 && $house['available_rooms'] <= 2) {
    $urgency = 'urgent';
    $label = 'Urgent';
} elseif ($house['available_rooms'] > 2 && $house['available_rooms'] <= 5) {
    $urgency = 'limited';
    $label = 'Limited';
}
?>

<?php if ($urgency): ?>
    <span class="u-badge <?= $urgency ?>">
        <?= ucfirst($urgency) ?>
    </span>
<?php endif; ?>

            <img src="<?php echo $house['image_path']; ?>" alt="House Image">

            <h3><?php echo htmlspecialchars($house['house_name']); ?></h3>

        
            <p><strong>Area:</strong> <?php echo htmlspecialchars($house['area']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($house['room_type']); ?></p>
            <p><strong>Price:</strong> From KES <?php echo number_format($house['price']); ?></p>
            <p><strong>Landlord:</strong> <?php echo htmlspecialchars($house['landlord_name']); ?></p>

            <p><?php echo nl2br(htmlspecialchars($house['description'])); ?></p>
            <div class="availability">
    <?php if ($house['available_rooms'] > 0): ?>
        <span class="badge available">
            🟢 <?php echo $house['available_rooms']; ?> room(s) available
        </span>
    <?php else: ?>
        <span class="badge full">
            🔴 Fully occupied
        </span>
    <?php endif; ?>
</div>

            <a href="view_room.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
                View Rooms
            </a>

        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No houses match your filters.</p>
<?php endif; ?>

</div>

</body>
</html>