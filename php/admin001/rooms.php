<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';

$houseId = isset($_GET['house_id']) ? intval($_GET['house_id']) : null;

$sql = "
SELECT r.*, h.house_name, h.house_id
FROM rooms r
LEFT JOIN houses h ON r.house_id = h.house_id
";

if ($houseId) {
    $sql .= " WHERE h.house_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $houseId);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 - Rooms</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
<a href="index.php" class="back-btn" title="Go back">← Back to Admin001</a>

<div class="admin-wrapper">
    <h2>Rooms <?= $houseId ? 'for House #' . $houseId : '' ?></h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>House</th>
                <th>Room Number</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['house_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['room_number']) ?></td>
                    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <form method="POST" action="room_action.php" style="display:inline;">
                            <input type="hidden" name="room_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
