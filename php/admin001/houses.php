<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';

$sql = "
SELECT h.*, l.full_name AS landlord_name
FROM houses h
LEFT JOIN landlords l ON h.landlord_id = l.id
ORDER BY h.house_id DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 - Houses</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
<a href="index.php" class="back-btn" title="Go back">← Back to Admin001</a>

<div class="admin-wrapper">
    <h2>All Houses</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Landlord</th>
                <th>Area</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['house_id'] ?></td>
                    <td><?= htmlspecialchars($row['house_name']) ?></td>
                    <td><?= htmlspecialchars($row['landlord_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['area']) ?></td>
                    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <form method="POST" action="house_action.php" style="display:inline;">
                            <input type="hidden" name="house_id" value="<?= $row['house_id'] ?>">
                            <input type="hidden" name="action" value="<?= $row['status'] === 'active' ? 'suspend' : 'activate' ?>">
                            <button type="submit" class="btn btn-warning">
                                <?= $row['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                            </button>
                        </form>

                        <form method="POST" action="house_action.php" style="display:inline; margin-left:6px;">
                            <input type="hidden" name="house_id" value="<?= $row['house_id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>

                        <a href="rooms.php?house_id=<?= $row['house_id'] ?>" class="btn" style="margin-left:6px;">Rooms</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
