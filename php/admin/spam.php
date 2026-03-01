<?php
session_start();
include '../toast.php';
require_once "admin_guard.php";
require '../db_connect.php';

// Fetch unresolved flags
$stmt = $conn->query("
    SELECT sf.*, 
           CASE 
               WHEN sf.user_type='landlord' THEN l.full_name
               WHEN sf.user_type='student' THEN s.full_name
           END as user_name
    FROM spam_flags sf
    LEFT JOIN landlords l 
        ON sf.user_id = l.id AND sf.user_type='landlord'
    LEFT JOIN students s 
        ON sf.user_id = s.id AND sf.user_type='student'
    WHERE sf.resolved=0
    ORDER BY sf.created_at DESC
");

$result = $stmt;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Spam Flags</title>
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        .low { color: green; font-weight: bold; }
        .medium { color: orange; font-weight: bold; }
        .high { color: red; font-weight: bold; }
        .btn { padding:6px 10px; text-decoration:none; border-radius:4px; }
        .suspend { background:#ff4d4d; color:white; }
        .resolve { background:#4CAF50; color:white; }
    </style>
</head>
<body>

<h2>Spam Detection Panel</h2>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Severity</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id']; ?></td>
    <td><?= htmlspecialchars($row['user_name'] ?? 'Unknown'); ?> (ID: <?= $row['user_id']; ?>)</td>
    <td><?= ucfirst($row['user_type']); ?></td>
    <td><?= htmlspecialchars($row['reason']); ?></td>
    <td class="<?= $row['severity']; ?>">
        <?= strtoupper($row['severity']); ?>
    </td>
    <td><?= $row['created_at']; ?></td>
    <td>
        <a class="btn suspend"
           href="suspend_user.php?type=<?= $row['user_type']; ?>&id=<?= $row['user_id']; ?>">
           Suspend
        </a>

        <a class="btn resolve"
           href="resolve_spam.php?id=<?= $row['id']; ?>">
           Mark Resolved
        </a>
    </td>
</tr>
<?php endwhile; ?>

    </tbody>
</table>

</body>
</html>