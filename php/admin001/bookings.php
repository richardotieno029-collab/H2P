<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';

$sql = "
SELECT b.*, s.full_name AS student_name, l.full_name AS landlord_name,
       r.room_number, h.house_name
FROM bookings b
LEFT JOIN students s ON b.student_internal_id = s.id
LEFT JOIN rooms r ON b.room_id = r.id
LEFT JOIN houses h ON r.house_id = h.house_id
LEFT JOIN landlords l ON h.landlord_id = l.id
ORDER BY b.created_at DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 - Bookings</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
<?php include '../toast.php'; ?>
<a href="index.php" class="back-btn" title="Go back">← Back to Admin001</a>

<div class="admin-wrapper">
    <h2>All Bookings</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Landlord</th>
                <th>House</th>
                <th>Room</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['landlord_name']) ?></td>
                    <td><?= htmlspecialchars($row['house_name']) ?></td>
                    <td><?= htmlspecialchars($row['room_number']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" action="booking_action.php" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn">Approve</button>
                        </form>
                        <form method="POST" action="booking_action.php" style="display:inline; margin-left:6px;">
                            <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
