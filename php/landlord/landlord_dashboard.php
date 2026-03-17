<?php
require_once "auth_landlord.php";
require_once "../db_connect.php";

$sql = "SELECT COUNT(*) AS total FROM notifications 
        WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$user_id = $_SESSION['user_id'];
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard | H2P</title>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../toast.php"; ?>

<?php include "dashboard_header.php"; ?>

   <div class="top-bar">
<button id="menu-toggle" class="menu-btn">
<i class="fa fa-bars"></i>
</button>

</div>
<div class="dash-wrapper">
 

    <aside id="sidebar" class="sidebar">
        <h3 class="logo">H2P Landlord</h3>

       <a href="manage_booking.php" class="btn-notify">
    📋 Manage Bookings
    <?php if ($count > 0): ?>
        <span class="notify-dot"></span>
    <?php endif; ?>
</a>
        <a href="add_house_form.php">➕ Add House</a>
        <a href="../index/about.php">About</a>
        <a href="../index/contact.php">Contact and Support</a>
        <a href="#">⚙ Settings</a>
    </aside>

    <main class="dash-content">
        <h2>My Houses</h2>

        <?php
$landlord_id = $_SESSION['user_id'];

$query = "
SELECT
    h.*,
    COUNT(DISTINCT v.id) AS views,
    COUNT(DISTINCT f.fav_id) AS favourites
FROM houses h
LEFT JOIN house_views v ON h.house_id = v.house_id
LEFT JOIN favourites f ON h.house_id = f.house_id
WHERE h.landlord_id = ?
GROUP BY h.house_id
ORDER BY h.house_id DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php if ($result->num_rows > 0): ?>
    <div class="houses-container">
        <?php while ($house = $result->fetch_assoc()): ?>
            <div class="house-card">
                 <a href="house_details.php?id=<?= $house['house_id']; ?>">
    <div class="card">
    <div class="image-wrapper">
                <img src="<?php echo $house['image_path']; ?>" alt="House Image">
        </div>
        </div>
        </a>

                <h3><?php echo htmlspecialchars($house['house_name']); ?></h3>

                <p><strong>Area:</strong> <?php echo $house['area']; ?></p>
                <p><strong>Type:</strong> <?php echo $house['room_type']; ?></p>
                <p><strong>Price:</strong> KES <?php echo $house['price']; ?></p>
                <p><strong>Views:</strong> <?php echo $house['views']; ?></p>
                <p><strong>Favourites:</strong> <?php echo $house['favourites']; ?></p>

                <div class="actions">
                    <a href="rooms.php?house_id=<?php echo $house['house_id']; ?>">View Rooms</a>
                    <a href="edit_house.php?id=<?php echo $house['house_id']; ?>">✏ Edit</a>
                    <a href="delete_house.php?id=<?php echo $house['house_id']; ?>" 
                       onclick="if (confirm('Delete this house?')) { showLoading('Deleting listing...'); return true; } return false;">🗑 Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>You haven’t added any houses yet.</p>
<?php endif; ?>
    </main>
</div>

<script>
const menuBtn = document.getElementById("menu-toggle");
const sidebar = document.getElementById("sidebar");
const content = document.querySelector(".dash-content");

menuBtn.addEventListener("click", function (e) {
    e.stopPropagation();
    sidebar.classList.toggle("active");
    content.classList.toggle("shift");
});

/* close sidebar when clicking anywhere else */
document.addEventListener("click", function (e) {

    if (
        sidebar.classList.contains("active") &&
        !sidebar.contains(e.target) &&
        e.target !== menuBtn
    ) {
        sidebar.classList.remove("active");
        content.classList.remove("shift");
    }

});

</script>

</body>
</html>