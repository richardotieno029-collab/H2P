<?php
session_start();
if (!isset($_SESSION['landlord_id'])) {
    header("Location: landlord_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "h2p");
$id = $_SESSION['landlord_id'];

$result = $conn->query("SELECT * FROM rooms WHERE landlord_id='$id'");
?>
<!DOCTYPE html>
<html>
<head>
<title>Landlord Dashboard</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Welcome <?php echo $_SESSION['landlord_name']; ?> 👋</h2>

<a href="add_house.php" class="button">+ Add New House</a>

<h3>Your Houses</h3>

<?php while ($row = $result->fetch_assoc()): ?>
<div class="house-card">
    <img src="uploads/<?php echo $row['image_path']; ?>" width="200">

    <p><strong><?php echo $row['description']; ?></strong></p>
    <p>Ksh: <?php echo $row['price']; ?></p>

    <a href="edit_house.php?room_id=<?php echo $row['room_id']; ?>">✏ Edit</a> |
    <a href="delete_house.php?room_id=<?php echo $row['room_id']; ?>" 
       onclick="return confirm('Delete this house?');">🗑 Delete</a>
</div>
<?php endwhile; ?>

</body>
</html>