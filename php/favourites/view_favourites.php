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

$student_id = $_SESSION['user_id'];

$query = "
SELECT 
    rooms.*,
    houses.house_name,
    houses.area,
    landlords.full_name AS landlord_name
FROM favourites
JOIN rooms ON favourites.room_id = rooms.id
JOIN houses ON rooms.house_id = houses.house_id
JOIN landlords ON houses.landlord_id = landlords.landlord_id
WHERE favourites.student_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Favourites</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../toast.php"; ?>
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
<header>
<h2>My Favourite Rooms</h2>
</header>
<div class="houses-container">
<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="house-card">
            <img 
            src="<?php echo htmlspecialchars($row['image_path']); ?>" 
            alt="Room Image"
        >


  <h3><?= htmlspecialchars($row['house_name']) ?></h3>
   <h3>Room Number: <?php echo htmlspecialchars($row['room_number']); ?></h3>
  <p><strong>Area:</strong> <?= $row['area'] ?></p>
  <p><strong>Landlord:</strong> <?= $row['landlord_name'] ?></p>
  <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>

  <!-- Favourite toggle -->
  <button 
    class="fav-btn active"
    data-room-id="<?= $row['id'] ?>">
    ♥
  </button>

      <script>
document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const roomId = this.dataset.roomId;

        fetch('../favourites/favourite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'room_id=' + roomId
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