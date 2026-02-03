<?php
session_start();
require_once "../db_connect.php";

/* =========================
   1️⃣ Validate house_id
========================= */
if (!isset($_GET['house_id']) || !is_numeric($_GET['house_id'])) {
    die("Invalid house selected.");
}

$house_id = (int) $_GET['house_id'];
$student_id = $_SESSION['user_id'] ?? null;

/* =========================
   2️⃣ Fetch house name
========================= */
$houseStmt = $conn->prepare(
    "SELECT house_name FROM houses WHERE house_id = ?"
);
$houseStmt->bind_param("i", $house_id);
$houseStmt->execute();
$houseResult = $houseStmt->get_result();

if ($houseResult->num_rows === 0) {
    die("House not found.");
}

$house = $houseResult->fetch_assoc();
$house_name = $house['house_name'];

/* =========================
   3️⃣ Fetch rooms in house
========================= */
$roomStmt = $conn->prepare(
    "SELECT id, room_number,image_path, status 
     FROM rooms 
     WHERE house_id = ?
     ORDER BY room_number ASC"
);
$roomStmt->bind_param("i", $house_id);
$roomStmt->execute();
$rooms = $roomStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rooms in <?= htmlspecialchars($house_name) ?></title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../dashboard_header.php"; ?>
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>

<h2 style="margin-bottom: 20px;">
    Rooms in <strong><?= htmlspecialchars($house_name) ?></strong>
</h2>

<?php if ($rooms->num_rows > 0): ?>
    <div class="houses-container">

        <?php while ($room = $rooms->fetch_assoc()): 
            $bookingStmt = $conn->prepare("
    SELECT status 
    FROM bookings 
    WHERE room_id = ? AND student_id = ?
    LIMIT 1
");
$bookingStmt->bind_param("ii", $room['id'], $student_id);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();
$myBooking = $bookingResult->fetch_assoc();?>
            <div class="house-card">
                <img 
            src="<?php echo htmlspecialchars($room['image_path']); ?>" 
            alt="Room Image"
        >

                <h3>Room <?= htmlspecialchars($room['room_number']) ?></h3>
<?php if ($myBooking && $myBooking['status'] === 'approved'): ?>
    <span class="Bbadge badge-available">✔ Reservation Successful</span>
    <a href="contact_landlord.php?room_id=<?= $room['id'] ?>" class="btn btn-success">
        Contact Landlord
    </a>

    <?php elseif ($myBooking && $myBooking['status'] === 'pending'): ?>
    <span class="Bbadge badge-pending">⏳ Awaiting Approval</span>
    

    <?php elseif ($room['status'] === 'occupied'): ?>
    <span class="Bbadge badge-occupied">❌ Occupied</span>
    

    <?php else: ?>
    <span class="Bbadge badge-available">🟢 Available</span>
    <p><form method="POST" action="book_room.php">
        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
        <input type="hidden" name="house_id" value="<?= $house_id ?>">
        <button class="book-btn">Book Room</button>
    </form></p>
<?php endif; ?>
               

    </br>  <button 
  class="fav-btn <?= $isFavourited ? 'active' : '' ?>"
  data-room-id="<?= $room['id'] ?>">
  ♥
</button>
<script>
document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const roomId = this.dataset.roomId;

        fetch('../favourites/favourite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'room_id=' + roomId
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'added') {
                this.classList.add('active');
            } else {
                this.classList.remove('active');
            }
        });
    });
});
</script>


            </div>
        <?php endwhile; ?>

    </div>

<?php else: ?>
    <p>No rooms have been added for this house yet.</p>
<?php endif; ?>

</body>
</html>
 