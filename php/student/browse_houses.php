<?php
require_once "auth_student.php";
require_once "../db_connect.php";

// Mark notifications as read
$clear = "UPDATE notifications SET is_read = 1 
          WHERE user_id = ?";
$stmt = $conn->prepare($clear);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

/* --------------------------
   FILTER LOGIC
---------------------------*/
$sql = "
SELECT 
    h.*,
    l.full_name AS landlord_name,

    /* TOTAL ROOMS */
    (SELECT COUNT(*) 
     FROM rooms r 
     WHERE r.house_id = h.house_id) AS total_rooms,

    /* AVAILABLE ROOMS */
    (SELECT COUNT(*) 
     FROM rooms r 
     WHERE r.house_id = h.house_id 
     AND r.status = 'vacant') AS available_rooms,

    /* VIEWS */
    (SELECT COUNT(*) 
     FROM house_views v 
     WHERE v.house_id = h.house_id) AS views,

    /* FAVOURITES */
    (SELECT COUNT(*) 
     FROM favourites f 
     WHERE f.house_id = h.house_id) AS favourites,

    /* PENDING BOOKINGS */
    CASE 
        WHEN EXISTS (
            SELECT 1
            FROM bookings b
            JOIN rooms r2 ON b.room_id = r2.id
            WHERE r2.house_id = h.house_id
              AND b.student_internal_id = ?
              AND b.status = 'pending'
        ) THEN 1 ELSE 0
    END AS has_pending

FROM houses h
JOIN landlords l ON h.landlord_id = l.id

WHERE h.status = 'active'
";
$types = "";

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

// Price range filter
if (!empty($_GET['price_range'])) {
    [$minPrice, $maxPrice] = explode('-', $_GET['price_range']);

    if (is_numeric($minPrice)) {
        $sql .= " AND h.price >= ?";
        $params[] = (int) $minPrice;
        $types .= "i";
    }

    if (is_numeric($maxPrice)) {
        $sql .= " AND h.price <= ?";
        $params[] = (int) $maxPrice;
        $types .= "i";
    }
}

// Only available houses
if (!empty($_GET['vacant'])) {
    $sql .= " AND (
        SELECT COUNT(*) 
        FROM rooms r 
        WHERE r.house_id = h.house_id 
        AND r.status = 'vacant'
    ) > 0";
}

$sql .= " ORDER BY has_pending DESC, h.created_at DESC";


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
    <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Houses</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include "../toast.php"; ?>
<a href="dashboard.php" class="back-btn" title="Go back">
    ←
</a>
<h2>Browse Houses</h2>

<!-- --------------------------
     FILTERS
--------------------------- -->
<form method="GET" class="filters">
    <select name="area">
        <option value="">All Areas</option>
        <option value="Bagik" <?= (isset($_GET['area']) && $_GET['area'] === 'Bagik') ? 'selected' : '' ?>>Bagik</option>
        <option value="Gakwegori" <?= (isset($_GET['area']) && $_GET['area'] === 'Gakwegori') ? 'selected' : '' ?>>Gakwegori</option>
        <option value="Spring Valley" <?= (isset($_GET['area']) && $_GET['area'] === 'Spring Valley') ? 'selected' : '' ?>>Spring Valley</option>
        <option value="Kamiu" <?= (isset($_GET['area']) && $_GET['area'] === 'Kamiu') ? 'selected' : '' ?>>Kamiu</option>
        <option value="Kangaru" <?= (isset($_GET['area']) && $_GET['area'] === 'Kangaru') ? 'selected' : '' ?>>Kangaru</option>
        <option value="Kayole" <?= (isset($_GET['area']) && $_GET['area'] === 'Kayole') ? 'selected' : '' ?>>Kayole</option>
        <option value="Njukiri" <?= (isset($_GET['area']) && $_GET['area'] === 'Njukiri') ? 'selected' : '' ?>>Njukiri</option>
        <option value="Leaders" <?= (isset($_GET['area']) && $_GET['area'] === 'Leaders') ? 'selected' : '' ?>>Leaders</option>
        <option value="Perez" <?= (isset($_GET['area']) && $_GET['area'] === 'Perez') ? 'selected' : '' ?>>Perez</option>
        <option value="Town" <?= (isset($_GET['area']) && $_GET['area'] === 'Town') ? 'selected' : '' ?>>Town</option>
        <option value="Other" <?= (isset($_GET['area']) && $_GET['area'] === 'Other') ? 'selected' : '' ?>>Other</option>
    </select>

    <select name="room_type">
        <option value="">All Room Types</option>
        <option value="Single" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'Single') ? 'selected' : '' ?>>Single</option>
        <option value="Bedsitter" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'Bedsitter') ? 'selected' : '' ?>>Bedsitter</option>
        <option value="One Bedroom" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'One Bedroom') ? 'selected' : '' ?>>1 Bedroom</option>
        <option value="Hostel" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'Hostel') ? 'selected' : '' ?>>Hostel</option>
    </select>

    <select name="price_range">
        <option value="">Any Price</option>
        <option value="0-2000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '0-2000') ? 'selected' : '' ?>>0 - 2,000</option>
        <option value="2000-4000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '2000-4000') ? 'selected' : '' ?>>2,000 - 4,000</option>
        <option value="4000-6000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '4000-6000') ? 'selected' : '' ?>>4,000 - 6,000</option>
        <option value="6000-8000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '6000-8000') ? 'selected' : '' ?>>6,000 - 8,000</option>
        <option value="8000-10000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '8000-10000') ? 'selected' : '' ?>>8,000 - 10,000</option>
        <option value="10000-12000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '10000-12000') ? 'selected' : '' ?>>10,000 - 12,000</option>
        <option value="12000-14000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '12000-14000') ? 'selected' : '' ?>>12,000 - 14,000</option>
        <option value="14000-16000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '14000-16000') ? 'selected' : '' ?>>14,000 - 16,000</option>
        <option value="16000-18000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '16000-18000') ? 'selected' : '' ?>>16,000 - 18,000</option>
        <option value="18000-20000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '18000-20000') ? 'selected' : '' ?>>18,000 - 20,000</option>
    </select>

    <label>
        <input type="checkbox" name="vacant" value="1"
            <?php if (!empty($_GET['vacant'])) echo "checked"; ?>>
        Only Available
    </label>

    <button type="submit" class="btn">Apply Filters</button>

<a href="browse_houses.php" class="clear-filters">Clear Filters</a>
</form>
<!-- --------------------------
     HOUSES LIST
--------------------------- -->
<div class="houses-container">

<?php if ($result->num_rows > 0): ?>
    <?php while ($house = $result->fetch_assoc()): ?>
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
<?php else: ?>
    <p>No houses match your filters.</p>
<?php endif; ?>

</div>
<script>

document.querySelectorAll('.fav-btn').forEach(button => {

button.addEventListener('click', function(e){

e.preventDefault();
e.stopPropagation();

const houseId = this.dataset.houseId;
const btn = this;

fetch('../favourites/toggle_favourite.php', {

method: 'POST',

headers: {
'Content-Type': 'application/x-www-form-urlencoded'
},

body: 'house_id=' + houseId

})

.then(res => res.json())

.then(data => {

if(data.status === 'added'){

btn.classList.add('active');

}else{

btn.classList.remove('active');

}

});

});

});

</script>

</body>
</html>