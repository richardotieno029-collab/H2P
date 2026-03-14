<?php
require_once "admin_guard.php";
require '../db_connect.php';

if (!isset($_GET['landlord_id'])) {
    die("Invalid request.");
}

$landlord_id = intval($_GET['landlord_id']);

$stmt = $conn->prepare("SELECT * FROM houses WHERE landlord_id=?");
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Houses</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>

<h2>Houses Owned By Landlord</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>House Name</th>
        <th>Location</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>
    <td><?= $row['house_id']; ?></td>
    <td><?= htmlspecialchars($row['house_name']); ?></td>
    <td><?= htmlspecialchars($row['area']); ?></td>
    <td><?= htmlspecialchars($row['status']); ?></td>
    <td>
        <a href="admin_rooms.php?house_id=<?= $row['house_id']; ?>" class="success">View Rooms</a> |
    
        <?php if ($row['status'] === 'active'): ?>
<a href="toggle_house.php?landlord_id=<?= $landlord_id ?>&house_id=<?= $row['house_id'] ?>&action=suspend" class="danger">
Suspend
</a>
<?php else: ?>
<a href="toggle_house.php?landlord_id=<?= $landlord_id ?>&house_id=<?= $row['house_id'] ?>&action=activate" class="success">
Activate
</a>
<?php endif; ?>
    </td>
</tr>

<?php endwhile; ?>

</table>

</body>
</html>