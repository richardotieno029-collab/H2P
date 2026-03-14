<?php
require '../session.php';
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
     $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'You are not authorised to access this page.'
        ];
    header("Location: ../index/index.php");
    exit;
}

$student_internal_id = $_SESSION['user_id'];

// Fetch favourite rooms with house and landlord details
$stmt = $conn->prepare("
    SELECT 
        houses.*,
        landlords.full_name AS landlord_name
    FROM favourites
    JOIN houses ON favourites.house_id = houses.house_id
    JOIN landlords ON houses.landlord_id = landlords.id
    WHERE favourites.student_internal_id = ?
");
$stmt->bind_param("i", $student_internal_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourites</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../toast.php"; ?>
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
<header>
<h2>My Favourite Houses</h2>
</header>
<div class="houses-container">
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        
<div class="house-card">
         <!-- show house details as in browse houses -->
<a href="../student/house_details.php?id=<?= $row['house_id']; ?>">
    <div class="card">
    <div class="image-wrapper">
        <img src="<?= $row['image_path']; ?>" alt="House Image">

        <div class="icon-overlay">
            <?php if($row['electricity_available'] == 1): ?>
                <i class="fas fa-bolt"></i>
            <?php endif; ?>

            <?php if($row['water_available'] == 1): ?>
                <i class="fas fa-tint"></i>
            <?php endif; ?>

            <?php if($row['wifi_available'] == 1): ?>
                <i class="fas fa-wifi"></i>
            <?php endif; ?>

            <?php if($row['hot_shower'] == 1): ?>
                <i class="fas fa-shower"></i>
            <?php endif; ?>
        </div>
    </div>
    </div>
</a>

            <h3><?php echo htmlspecialchars($row['house_name']); ?></h3>

        <p><strong>Landlord:</strong> <?php echo htmlspecialchars($row['landlord_name']); ?></p>
            <p><strong>Area:</strong> <?php echo htmlspecialchars($row['area']); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($row['room_type']); ?></p>
            <p><strong>Price:</strong> From KES <?php echo number_format($row['price']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
<a href="../student/view_room.php?house_id=<?php echo $row['house_id']; ?>" class="btn">
                View Rooms
            </a>
  <!-- Favourite toggle -->
  <button 
    class="fav-btn active"
    data-house-id="<?= $row['house_id'] ?>">
    ♥
  </button>

      <script>
document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const houseId = this.dataset.houseId;

        fetch('toggle_favourite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'house_id=' + houseId
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'added') {
                this.classList.add('active');
            } else {
                this.classList.remove('active');
            }
        });
    });
});
</script>


</div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="dashboard-actions">

        <a href="../student/browse_houses.php" class="dash-card">
            <h3>🔍 Browse Houses</h3>
            <p>No favourites yet. Browse houses to add</p>
        </a>
    <?php endif; ?>
</div>

</body>
</html>