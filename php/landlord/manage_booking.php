<?php
require_once "auth_landlord.php";
require_once "../db_connect.php";

$landlord_id = $_SESSION['user_id'];
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
// Mark notifications as read
$clear = "UPDATE notifications SET is_read = 1 
          WHERE user_id = ?";
$stmt = $conn->prepare($clear);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

$sql = "
SELECT 
    b.id AS booking_id,
    b.status AS booking_status,
    s.full_name AS student_name,
    r.room_number,r.image_path,r.status AS room_status,
     TIMESTAMPDIFF(SECOND, NOW(), b.expires_at) AS seconds_left,
    h.house_name
FROM bookings b
JOIN rooms r ON b.room_id = r.id
JOIN houses h ON r.house_id = h.house_id
JOIN students s ON b.student_internal_id = s.id
WHERE h.landlord_id = ?
AND b.status = 'pending'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="../styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .countdown {
            padding: 5px 10px;
            border-radius: 4px;
            background-color: #f0ad4e;
            color: white;
            font-weight: bold;
        }
        .badge-urgent {
            background-color: #d9534f !important;
        }
        .badge-expired {
            background-color: #6c757d !important;
        }
        .no-booking {
    text-align: center;
    padding: 40px;
    background: #f8f8f8;
    border-radius: 8px;
    color: #666;
    font-size: 16px;
}
    </style>
</head>
<body>
    <?php include "../toast.php"; ?>
    <a href="landlord_dashboard.php" class="back-btn" title="Go back">
    ←
</a>
<header>
<h2>My Bookings</h2>
</header>
<div class="dash-container">

<?php if ($result->num_rows === 0): ?>
    <div class="no-booking">
    <p>No pending bookings at the moment.</p>
    </div>
<?php else: ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>Image</th>
            <th>House Name</th>
            <th>Room Number</th>
            <th>Student Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <img src="<?php echo $row['image_path']; ?>" class="room-img">
            </td>
            <td><?= htmlspecialchars($row['house_name']) ?></td>
            <td><?= $row['room_number'] ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td>
                <?php if ($row['booking_status'] === 'pending'): ?>
                    
                    <span 
                        class="countdown"
                        data-seconds="<?= max(0, (int)$row['seconds_left']) ?>">
                        ⏳ Pending
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <div class="actions">
                <a href="approve_booking.php?id=<?= $row['booking_id'] ?>" class="book-btn" onclick="showLoading('Approving booking...')">Approve</a>
                <a href="reject_booking.php?id=<?= $row['booking_id'] ?>" class="btn btn-danger" 
                   onclick="if (confirm('Are you sure you want to reject this booking?')) { showLoading('Rejecting request...'); return true; } return false;">Reject</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>
</div>
<script>
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
</body>
</html>