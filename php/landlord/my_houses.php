<?php
require_once "auth_landlord.php";
include '../config/db.php';

$landlord_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM rooms WHERE landlord_id = ?");
$stmt->execute([$landlord_id]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Houses</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include "../toast.php"; ?>
<header>
    <h1>My Houses</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_house.php">Add House</a>
        <a href="my_houses.php" class="active">My Houses</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="my-houses-container">

    <h2>Your Uploaded Houses</h2>

    <?php if (count($rooms) === 0): ?>
        <p>You haven't added any houses yet.</p>
    <?php else: ?>

        <div class="rooms-grid">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <img src="../<?php echo $room['image_path']; ?>" alt="Room image">
                    <h3><?php echo $room['house_name']; ?></h3>
                    <p><strong>Type:</strong> <?php echo $room['room_type']; ?></p>
                    <p><strong>Price:</strong> KSh <?php echo $room['price']; ?></p>
                    <p><strong>Status:</strong> <?php echo $room['status']; ?></p>

                    <a href="edit_house.php?id=<?php echo $room['id']; ?>" class="btn edit">Edit</a>
                    <a href="delete_house.php?id=<?php echo $room['id']; ?>" class="btn delete" onclick="if (confirm('Delete this house?')) { showLoading('Deleting listing...'); return true; } return false;">Delete</a>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

</body>
</html>