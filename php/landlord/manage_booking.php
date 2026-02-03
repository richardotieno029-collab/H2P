<?php
session_start();
require_once "../db_connect.php";

if ($_SESSION['user_role'] !== 'landlord') {
    header("Location: ../index/index.php");
    exit;
}

$landlord_id = $_SESSION['user_id'];
// Mark notifications as read
$clear = "UPDATE notifications SET is_read = 1 
          WHERE user_id = ?";
$stmt = $conn->prepare($clear);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

$sql = "
SELECT 
    b.id AS booking_id,
    s.full_name AS student_name,
    r.room_number,r.image_path,
    h.house_name
FROM bookings b
JOIN rooms r ON b.room_id = r.id
JOIN houses h ON r.house_id = h.house_id
JOIN students s ON b.student_id = s.student_id
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
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
<header>
<h2>My Bookings</h2>
</header>
<div class="houses-container">
    <?php while ($row = $result->fetch_assoc()): ?>
<div class="booking-card">
    <table border="1" cellpadding="10">
    <tr>
        <th>Image</th>
        <th>House Name</th>
        <th>Room Number</th>
        <th>Student Name</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <tr>
        <td>
            <img src="<?php echo $row['image_path']; ?>" class="room-img">
        </td>
        <td class="type">
    <h4><?= htmlspecialchars($row['house_name']) ?></h4>
    </td>
    <td class="type">
    <p><strong>Room:</strong> <?= $row['room_number'] ?></p>
    </td>
    <td class="type">
    <p><strong>Student:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
    </td>
    <td>
    <span class="Bbadge badge-pending">Pending</span>
    </td>
    <td>
    <div class="actions">
        <a href="approve_booking.php?id=<?= $row['booking_id'] ?>" class="book-btn">Approve</a>
        <a href="reject_booking.php?id=<?= $row['booking_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this booking?')">Reject</a>
    </div>
    </td>
    </tr>
    </table>

</div>
<?php endwhile; ?>
</div>
</body>
</html>