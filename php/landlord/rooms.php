<?php
session_start();
include "../db_connect.php";

/* Protect */
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

/* Validate house */
if (!isset($_GET['house_id'])) {
    die("House not specified.");
}

$house_id = (int)$_GET['house_id'];

/* Fetch rooms */
$sql = "SELECT * FROM rooms WHERE house_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $house_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Rooms</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../dashboard_header.php"; ?>
<div class="dash-wrapper">

    <aside class="sidebar">

<a href="add_room_form.php?house_id=<?php echo $house_id; ?>">
    ➕ Add Room
</a>
<a href="landlord_dashboard.php">⬅ Back to Dashboard</a>
</aside>
<br><br>

<main class="dash-content">
<h2>Rooms in This House</h2>
<?php if ($result->num_rows == 0): ?>
    <p>No rooms added yet.</p>
<?php else: ?>

<table border="1" cellpadding="10">
    <tr>
        <th>Image</th>
        <th>Type</th>
        <th>Price</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

 <?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td>
        <img src="../../<?= $row['image_path']; ?>" class="room-img">
    </td>

    <td class="type">
        <?= htmlspecialchars($row['room_type']); ?>
    </td>

    <td class="price">
        KES <?= number_format($row['price']); ?>
    </td>

    <td>
        <span class="status <?= strtolower($row['status']); ?>">
            <?= htmlspecialchars($row['status']); ?>
        </span>
    </td>

    <td class="actions">
        <a href="edit_room.php?id=<?= $row['id']; ?>" class="edit">Edit</a> |
        <a href="delete_room.php?id=<?= $row['id']; ?>" 
           class="delete"
           onclick="return confirm('Delete this room?')">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>

<?php endif; ?>

    </main>

</body>
</html>