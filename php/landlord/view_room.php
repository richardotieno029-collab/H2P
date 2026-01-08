<?php
// db connection
require_once "../db_connect.php";

// validate house_id
if (!isset($_GET['house_id']) || !is_numeric($_GET['house_id'])) {
    die("Invalid house selected.");
}

$house_id = (int) $_GET['house_id'];

// fetch house details (optional but nice UX)
$houseStmt = $conn->prepare("SELECT house_name location FROM houses WHERE house_id = ?");
$houseStmt->bind_param("i", $house_id);
$houseStmt->execute();
$house = $houseStmt->get_result()->fetch_assoc();

if (!$house) {
    die("House not found.");
}

// fetch rooms under this house
$roomStmt = $conn->prepare("
    SELECT id, room_type, price, status, image_path
    FROM rooms
    WHERE house_id = ?
    ORDER BY id DESC
");
$roomStmt->bind_param("i", $house_id);
$roomStmt->execute();
$rooms = $roomStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rooms in <?php echo htmlspecialchars($house['house_name']); ?></title>
<link rel="stylesheet" href="../../public/assets/styles.css">
</head>
<body>

<h2>
    Rooms in <?= htmlspecialchars($house['house_name'] ?? 'this house') ?>
</h2>
<p><?php echo htmlspecialchars($house['location']); ?></p>

<hr>

<?php if ($rooms->num_rows === 0): ?>
    <p>No rooms available in this house yet.</p>
<?php else: ?>

<div class="rooms-container">

<?php while ($room = $rooms->fetch_assoc()): 
    $occupied = ($room['status'] === 'occupied');
?>
    <div class="room-card <?php echo $occupied ? 'occupied' : ''; ?>">

        <img 
            src="../../<?php echo htmlspecialchars($room['image_path']); ?>" 
            alt="Room Image"
        >

        <h3><?php echo htmlspecialchars($room['room_type']); ?></h3>

        <p class="price">
            KES <?php echo number_format($room['price']); ?>
        </p>

        <p class="status <?php echo $occupied ? 'status-occupied' : 'status-vacant'; ?>">
            <?php echo ucfirst($room['status']); ?>
        </p>

        <?php if ($occupied): ?>
            <button class="btn disabled" disabled>
                Not Available
            </button>
        <?php else: ?>
            <a href="tel:+254XXXXXXXXX" class="btn">
              Vacant Contact Landlord
            </a>
        <?php endif; ?>

    </div>
<?php endwhile; ?>

</div>
<?php endif; ?>

</body>
</html>