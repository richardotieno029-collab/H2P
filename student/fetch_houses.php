<?php
require_once "auth_student.php";

$params = [];
$types = "";

/* MAIN QUERY */
$sql = "
SELECT 
    h.*,
    l.full_name AS landlord_name,

    COUNT(DISTINCT r.id) AS total_rooms,

    (SELECT COUNT(*) FROM rooms r2 
     WHERE r2.house_id = h.house_id 
     AND r2.status = 'vacant') AS available_rooms,

    COUNT(DISTINCT v.id) AS views,
    COUNT(DISTINCT f.fav_id) AS favourites,

    CASE 
        WHEN EXISTS (
            SELECT 1
            FROM bookings b
            JOIN rooms r3 ON b.room_id = r3.id
            WHERE r3.house_id = h.house_id
            AND b.student_internal_id = ?
            AND b.status = 'pending'
        ) THEN 1 ELSE 0
    END AS has_pending

FROM houses h
JOIN landlords l ON h.landlord_id = l.id

LEFT JOIN rooms r ON h.house_id = r.house_id
LEFT JOIN house_views v ON h.house_id = v.house_id
LEFT JOIN favourites f ON h.house_id = f.house_id

WHERE h.status = 'active'
";

/* PARAM */
$params[] = $_SESSION['user_id'];
$types .= "i";

//fetch favourites
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

/* FILTERS */

if (!empty($_GET['area'])) {
    $sql .= " AND h.area = ?";
    $params[] = $_GET['area'];
    $types .= "s";
}

if (!empty($_GET['room_type'])) {
    $sql .= " AND h.room_type = ?";
    $params[] = $_GET['room_type'];
    $types .= "s";
}

if (!empty($_GET['price_range'])) {
    [$minPrice, $maxPrice] = explode('-', $_GET['price_range']);

    if (is_numeric($minPrice)) {
        $sql .= " AND h.price >= ?";
        $params[] = (int)$minPrice;
        $types .= "i";
    }

    if (is_numeric($maxPrice)) {
        $sql .= " AND h.price <= ?";
        $params[] = (int)$maxPrice;
        $types .= "i";
    }
}

if (!empty($_GET['vacant'])) {
    $sql .= " AND (
        SELECT COUNT(*) FROM rooms r2
        WHERE r2.house_id = h.house_id
        AND r2.status = 'vacant'
    ) > 0";
}

$sql .= " GROUP BY h.house_id";
$sql .= " ORDER BY has_pending DESC, h.created_at DESC";

/* EXECUTE */
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
/* OUTPUT CARDS ONLY */
while ($house = $result->fetch_assoc()): ?>

<div class="house-card <?= $house['has_pending'] ? 'pending-house' : '' ?>">
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

            <h3><?php echo htmlspecialchars($house['house_name']); ?></h3>
            <p><strong>Area:</strong> <?php echo htmlspecialchars($house['area']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($house['room_type']); ?></p>
            <p><strong>Price:</strong> From KES <?php echo number_format($house['price']); ?></p>
            <p><strong>Deposit:</strong> KES <?php echo number_format($house['deposit']); ?></p>
            <p><strong>Landlord:</strong> <?php echo htmlspecialchars($house['landlord_name']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($house['description'])); ?></p>
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
<button 
class="fav-btn <?= in_array($house['house_id'], $favHouses) ? 'active' : '' ?>"
data-house-id="<?= $house['house_id'] ?>">
<i class="fa fa-heart"></i>
</button>
</div>

<?php endwhile; ?>
</div>
<?php else: ?>

<div class="no-results">
    <h3>😕 No houses found</h3>
    <p>Try adjusting your filters</p>
</div>

<?php endif; ?>