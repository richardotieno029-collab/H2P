<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';

$type = $_GET['type'] ?? 'student';
$table = $type === 'landlord' ? 'landlords' : 'students';

$result = $conn->query("SELECT * FROM $table ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 - Manage Users</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
    <?php include '../toast.php'; ?>
<a href="index.php" class="back-btn" title="Go back">← Back to Admin001</a>

<div class="admin-wrapper">
    <h2>Manage <?= ucfirst($type) ?>s</h2>

    <div style="margin-bottom: 16px;">
        <a href="users.php?type=student" class="btn">Students</a>
        <a href="users.php?type=landlord" class="btn">Landlords</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Risk Score</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['risk_score']) ?></td>
                    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <form method="POST" action="user_action.php" style="display:inline;">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="<?= $row['status'] === 'active' ? 'suspend' : 'activate' ?>">
                            <button type="submit" class="btn btn-warning">
                                <?= $row['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                            </button>
                        </form>

                        <form method="POST" action="user_action.php" style="display:inline; margin-left:6px;">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
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
