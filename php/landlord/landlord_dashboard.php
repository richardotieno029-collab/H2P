<?php
require_once "../session.php";
require_once "../db_connect.php";

if ($_SESSION['user_role'] !== 'landlord') {
    header("Location: ../index/index.php");
    exit;

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Dashboard | H2P</title>
 
   <link rel="stylesheet" href="../styles.css">

   <script>
const btn = document.getElementById("accountBtn");
const dropdown = document.getElementById("accountDropdown");

btn.addEventListener("click", function (e) {
    e.stopPropagation();
    dropdown.classList.toggle("show");
});

// close dropdown when clicking elsewhere
document.addEventListener("click", function () {
    dropdown.classList.remove("show");
});
</script>
</head>
<body>

<?php include "../dashboard_header.php"; ?>
<div class="dash-wrapper">

    <aside class="sidebar">
        <h3 class="logo">H2P Admin</h3>

        <a href="../index/index.php" class="active">🏠 Home</a>
        <a href="add_house_form.php">➕ Add House</a>
        <a href="#">⚙ Settings</a>
    </aside>

    <main class="dash-content">
        <h2>My Houses</h2>

        <?php
$landlord_id = $_SESSION['user_id'];

$query = "SELECT * FROM houses WHERE landlord_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
$house_id = (int)$_SESSION['house_id'];
?>
<?php if ($result->num_rows > 0): ?>
    <div class="houses-container">
        <?php while ($house = $result->fetch_assoc()): ?>
            <div class="house-card">
                <img src="<?php echo $house['image_path']; ?>" alt="House Image">

                <h3><?php echo htmlspecialchars($house['house_name']); ?></h3>

                <p><strong>Area:</strong> <?php echo $house['area']; ?></p>
                <p><strong>Type:</strong> <?php echo $house['room_type']; ?></p>
                <p><strong>Price:</strong> KES <?php echo $house['price']; ?></p>

                <div class="actions">
                    <a href="rooms.php?house_id=<?php echo $house['house_id']; ?>">View Rooms</a>
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