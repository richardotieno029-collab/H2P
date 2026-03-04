<?php
require_once 'admin_guard.php';
require_once '../db_connect.php';

$type = $_GET['type'] ?? 'student';

$table = $type === 'landlord' ? 'landlords' : 'students';

$result = $conn->query("SELECT * FROM $table ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>
<link rel="stylesheet" href="admin_styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">

<h2>
<i class="fa-solid fa-users"></i>
Managing <?= ucfirst($type) ?>s
</h2>

<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Risk Score</th>
<th>Status</th>
<th>Action</th>
<th>Houses</th>
</tr>
</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>

<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['risk_score']) ?></td>

<td>
<span class="status <?= $row['status'] ?>">
<?= $row['status'] ?>
</span>
</td>

<td>

<?php if ($row['status'] === 'active'): ?>
<a href="toggle_user.php?type=<?= $type ?>&id=<?= $row['id'] ?>&action=suspend" class="danger">
Suspend
</a>
<?php else: ?>
<a href="toggle_user.php?type=<?= $type ?>&id=<?= $row['id'] ?>&action=activate" class="success">
Activate
</a>
<?php endif; ?>
</td>
<td>
<?php if ($type === 'landlord'): ?>
    <a href="admin_houses.php?landlord_id=<?= $row['id']; ?>" 
   class="success">
   View Houses
</a>
<?php else: ?>
    N/A
<?php endif; ?>
</td>

</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>

</body>
</html>