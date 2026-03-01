<?php
require_once "admin_guard.php";
require '../db_connect.php';

$house_id = intval($_GET['house_id']);

$stmt = $conn->prepare("SELECT * FROM rooms WHERE house_id=?");
$stmt->bind_param("i", $house_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Rooms in House</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
 <a href="dashboard.php" class="back-btn" title="Go back">
    Back to Dashboard
</a>
<h2>Rooms in House ID: <?= $house_id ?></h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Room Number</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>
    <td><?= $row['id']; ?></td>
    <td><?= htmlspecialchars($row['room_number']); ?></td>
    <td><?= htmlspecialchars($row['status']); ?></td>
    <td>
        <a href="../guest/browse_houses.php?house_id=<?= $row['house_id']; ?>" class="success">Inspect</a> |
<a href="delete_house.php?house_id=<?= $house_id ?>" class="danger" onclick="return confirm('Are you sure you want to delete this house and all its rooms?');">
Delete House
</a>
    </td>
</tr>

<?php endwhile; ?>

</table>

</body>
</html>