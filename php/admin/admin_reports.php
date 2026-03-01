<?php
require_once 'admin_guard.php';
require_once '../db_connect.php';

$result = $conn->query("SELECT * FROM reports ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin - Reports</title>
<link rel="stylesheet" href="admin_styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="admin-container">
<h2><i class="fa-solid fa-flag"></i> User Reports</h2>

<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Subject</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['subject']) ?></td>
<td>
<span class="status <?= $row['status'] ?>">
<?= $row['status'] ?>
</span>
</td>
<td><?= $row['created_at'] ?></td>
<td>
<a href="view_report.php?id=<?= $row['id'] ?>" class="view-btn">
<i class="fa-solid fa-eye"></i>
</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>

</body>
</html>