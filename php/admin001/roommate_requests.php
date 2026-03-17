<?php
require_once 'admin001_guard.php';
require_once '../db_connect.php';

$sql = "
SELECT r.*, 
       s.full_name AS guest_name, 
       h.student_id AS host_student_id,
       hs.full_name AS host_name,
       hs.status AS host_status
FROM roommate_requests r
LEFT JOIN students s ON r.student_id = s.id
LEFT JOIN roommate_hosts h ON r.host_id = h.host_id
LEFT JOIN students hs ON h.student_id = hs.id
ORDER BY r.created_at DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 - Roommate Requests</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
<?php include '../toast.php'; ?>
<a href="index.php" class="back-btn" title="Go back">← Back to Admin001</a>

<div class="admin-wrapper">
    <h2>Roommate Requests</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest</th>
                <th>Host</th>
                <th>Host Status</th>
                <th>Request Status</th>
                <th>Requested At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['request_id'] ?></td>
                    <td><?= htmlspecialchars($row['guest_name']) ?></td>
                    <td><?= htmlspecialchars($row['host_name']) ?></td>
                    <td><?= htmlspecialchars($row['host_status']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <?php if ($row['status'] === 'PENDING'): ?>
                            <form method="POST" action="roommate_request_action.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn">Approve</button>
                            </form>
                            <form method="POST" action="roommate_request_action.php" style="display:inline; margin-left:6px;">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
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
