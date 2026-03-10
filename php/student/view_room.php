<?php
require_once "auth_student.php";
require_once "../db_connect.php";

/* =========================
   1️⃣ Validate house_id
========================= */
if (!isset($_GET['house_id']) || !is_numeric($_GET['house_id'])) {
    die("Invalid house selected.");
}

$house_id = (int) $_GET['house_id'];
$student_internal_id = $_SESSION['user_id'] ?? null;

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
//auto expire on idle student after approval
$conn->query("
    UPDATE rooms r
    JOIN bookings b ON r.id = b.room_id
    SET 
        b.status = 'expired',
        r.status = 'vacant'
    WHERE 
        b.status = 'approved'
        AND b.approved_expires_at < NOW()
");
/* expiration of booking */
$conn->query("
    UPDATE bookings b
JOIN rooms r ON b.room_id = r.id
JOIN (
    SELECT MIN(id) AS id
    FROM bookings
    WHERE status = 'pending' AND expires_at < NOW()
    GROUP BY room_id
) x ON b.id = x.id
SET 
    b.status = 'expired',
    r.status = 'vacant';
");
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
    <?php include "../toast.php"; ?>
    <a href="browse_houses.php" class="back-btn" title="Go back">
    ←
</a>

<h2 style="margin-bottom: 20px;">
    Rooms in <strong><?= htmlspecialchars($house_name) ?></strong>
</h2>

<?php if ($rooms->num_rows > 0): ?>
    <div class="houses-container">

        <?php while ($room = $rooms->fetch_assoc()): 

$bookingStmt = $conn->prepare("
    SELECT 
        status,id,
        TIMESTAMPDIFF(SECOND, NOW(), expires_at) AS seconds_left
    FROM bookings
    WHERE room_id = ? AND student_internal_id = ? AND STATUS IN ('pending', 'approved')
    ORDER BY created_at DESC
    LIMIT 1
");
$bookingStmt->bind_param("is", $room['id'], $student_internal_id);
$bookingStmt->execute();
$myBooking = $bookingStmt->get_result()->fetch_assoc();?>
            <div class="house-card">
                 <!-- 🔖 TOP LEFT BADGE -->
    <?php if ($myBooking && $myBooking['status'] === 'pending'): ?>
        <span class="Bbadge badge-pending countdown"
              data-seconds="<?= max(0, (int)$myBooking['seconds_left']) ?>">
            ⏳ Awaiting approval
        </span>

    <?php elseif ($myBooking && $myBooking['status'] === 'approved'): ?>
        <span class="Bbadge badge-approved">
            ✅ Approved
        </span>

    <?php elseif ($room['status'] === 'occupied'): ?>
        <span class="Bbadge badge-occupied">
            Occupied
        </span>

    <?php elseif ($room['status'] === 'pending'): ?>
        <span class="Bbadge badge-selected">
            🟡 Reserved
        </span>

    <?php else: ?>
        <span class="Bbadge badge-available">
            🟢 Available
        </span>
    <?php endif; ?>

    <a href="room_details.php?id=<?= $room['id']; ?>">
    <div class="card">
    <div class="image-wrapper">
                <img 
            src="<?php echo htmlspecialchars($room['image_path']); ?>" 
            alt="Room Image"
        >
    </div>
    </div>
    </a>

                <h3>Room <?= htmlspecialchars($room['room_number']) ?></h3>
<div class="house-card">

    <!-- 🔘 ACTIONS AT BOTTOM -->
    <div class="room-actions">

        <?php if ($myBooking && $myBooking['status'] === 'approved'): ?>
            <a href="contact_landlord.php?room_id=<?= $room['id'] ?>"
               class="Bbadge badge-approved">
                Contact landlord
            </a>

            <?php elseif ($myBooking && $myBooking['status'] === 'pending'): ?>
    <form method="POST" action="cancel_booking.php">
        <input type="hidden" name="booking_id" value="<?= $myBooking['id'] ?>">
        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
        <button class="booked-btn">
            Cancel Request
        </button>
    </form>

        <?php elseif ($room['status'] === 'pending'): ?>
        <button class="pending-btn">
            Resets after 24hrs
        </button>

        <?php elseif ($room['status'] === 'occupied'): ?>
            <button class="booked-btn">
            Not Available
        </button>

        <?php elseif (!$myBooking && $room['status'] === 'vacant'): ?>
            <form method="POST" action="book_room.php">
                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
        <input type="hidden" name="house_id" value="<?= $house_id ?>">
                <button class="book-btn">
                    Book room
                </button>
            </form>

        <?php endif; ?>

    </div>
</div>
<script>
// Countdown timer for pending bookings
document.querySelectorAll('.countdown').forEach(badge => {
    let seconds = parseInt(badge.dataset.seconds);
    const timer = setInterval(() => {
        if (seconds <= 0) {
            badge.innerText = "⌛ Expired";
            badge.classList.add("badge-expired");
            clearInterval(timer);
            return;
        }

        let h = Math.floor(seconds / 3600);
        let m = Math.floor((seconds % 3600) / 60);
        let s = seconds % 60;

        badge.innerText = `⏳ ${h}h ${m}m ${s}s left`;

        if (seconds < 3600) {
            badge.classList.add("badge-urgent");
        }

        seconds--;
    }, 1000);
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
 