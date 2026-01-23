<?php
session_start();

/* Logged-in student (null if guest) */
$student_id = $_SESSION['user_id'] ?? null;

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
    <title>Rooms in <?php echo htmlspecialchars($house['location']); ?></title>
<link rel="stylesheet" href="../styles.css">
<style>
    .fav-btn {
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #aaa; /* grey */
    transition: transform 0.2s, color 0.2s;
}

.fav-btn:hover {
    transform: scale(1.2);
}

.fav-active {
    color: #e63946; /* red */
}
</style>
</head>
<body>
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
    <?php include "../dashboard_header.php"; ?>

<h2>
Rooms in <?php echo htmlspecialchars($house['location']); ?>
</h2>

<hr>

<?php if ($rooms->num_rows === 0): ?>
    <p>No rooms available in this house yet.</p>
<?php else: ?>

<div class="houses-container">

<?php while ($room = $rooms->fetch_assoc()): 
    $occupied = ($room['status'] === 'occupied');
?>
    <div class="house-card <?php echo $occupied ? 'occupied' : ''; ?>">

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
            </button></br>
            
        <?php else: ?>
            <a href="tel:+254XXXXXXXXX" class="btn">
              Vacant Contact Landlord
            </a></br>
            <?php endif; ?>
<?php
$student_id = $_SESSION['user_id'];
$favCheck = $conn->prepare("
    SELECT fav_id FROM favourites
    WHERE student_id = ? AND room_id = ?
");
$favCheck->bind_param("si", $student_id, $room['id']);
$favCheck->execute();
$favResult = $favCheck->get_result();

$isFavourite = $favResult->num_rows > 0;
?>
<?php
if ($isFavourite): ?>
    <form action="../favourites/remove_favourite.php" method="POST">
        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
        <button class="fav-btn fav-active">❤️</button>
    </form>
<?php else: ?>
    <form action="../favourites/add_favourites.php" method="POST">
        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
        <button class="fav-btn">🤍</button>
    </form>

            <?php if (isset($_SESSION['user_id'])): ?>

<?php endif; ?>

            <?php endif; ?>


    </div>
<?php endwhile; ?>

</div>
<?php endif; ?>

</body>
</html>