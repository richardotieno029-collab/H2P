<?php
require_once "../session.php";

if (!isset($_SESSION['landlord_id'])) {
    header("Location: ../../public/landlord/landlord_login.html");
    exit;
}
$landlord_id = $_SESSION['landlord_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Dashboard | H2P</title>
    <link rel="stylesheet" href="../../public/assets/styles.css">
</head>
<body>

<div class="dash-wrapper">

    <aside class="sidebar">
        <h3 class="logo">H2P Admin</h3>

        <a href="../index.html" class="active">🏠 Home</a>
        <a href="add_house_form.php">➕ Add House</a>
        <a href="#">⚙ Settings</a>
        <a href="landlord_login.html" class="logout">🚪 Logout</a>
    </aside>

    <main class="dash-content">
        <h2>My Houses</h2>

        <?php
$landlord_id = $_SESSION['landlord_id'];

$query = "SELECT * FROM houses WHERE landlord_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php if ($result->num_rows > 0): ?>
    <div class="houses-container">
        <?php while ($house = $result->fetch_assoc()): ?>
            <div class="house-card">
                <a href="rooms.php"><img src="<?php echo $house['image_path']; ?>" alt="House Image"></a>

                <h3><?php echo htmlspecialchars($house['house_name']); ?></h3>

                <p><strong>Area:</strong> <?php echo $house['area']; ?></p>
                <p><strong>Type:</strong> <?php echo $house['room_type']; ?></p>
                <p><strong>Price:</strong> KES <?php echo $house['price']; ?></p>

                <div class="actions">
                    <a href="edit_house.php?id=<?php echo $house['house_id']; ?>">✏ Edit</a>
                    <a href="delete_house.php?id=<?php echo $house['house_id']; ?>" 
                       onclick="return confirm('Delete this house?')">🗑 Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>You haven’t added any houses yet.</p>
<?php endif; ?>
    </main>

</body>
</html>