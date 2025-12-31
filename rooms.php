<?php
// details.php
require_once "../db_connect.php";

// 1. Validate house_id
if (!isset($_GET['house_id'])) {
    die("House not specified.");
}

$house_id = intval($_GET['house_id']);

// 2. Fetch house details
$houseStmt = $conn->prepare("SELECT * FROM houses WHERE house_id = ?");
$houseStmt->bind_param("i", $house_id);
$houseStmt->execute();
$houseResult = $houseStmt->get_result();

if ($houseResult->num_rows === 0) {
    die("House not found.");
}

$house = $houseResult->fetch_assoc();

// 3. Fetch rooms under this house
$roomStmt = $conn->prepare("SELECT * FROM rooms WHERE house_id = ?");
$roomStmt->bind_param("i", $house_id);
$roomStmt->execute();
$roomsResult = $roomStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($house['house_name']) ?> | H2P</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- reuse your master css -->
    <link rel="stylesheet" href="../../public/assets/styles.css">

    <style>
        /* Minimal local styling (can move to master css later) */
        .house-header {
            padding: 20px;
            background: #f5f5f5;
            margin-bottom: 20px;
        }

        .rooms-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .room-card {
            width: 280px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .room-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .room-card .content {
            padding: 15px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .vacant {
            background: #d4edda;
            color: #155724;
        }

        .occupied {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

<header>
    <h1>House Details</h1>
    <nav>
        <a href="index.php">← Back to Home</a>
    </nav>
</header>

<main>

    <!-- HOUSE INFO -->
    <section class="house-header">
        <h2><?= htmlspecialchars($house['house_name']) ?></h2>
        <p><strong>Area:</strong> <?= htmlspecialchars($house['area']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($house['description']) ?></p>
    </section>

    <!-- ROOMS -->
    <section>
        <h3>Rooms</h3>

        <?php if ($roomsResult->num_rows === 0): ?>
            <p>No rooms added for this house yet.</p>
        <?php else: ?>
            <div class="rooms-grid">

                <?php while ($room = $roomsResult->fetch_assoc()): ?>
                    <div class="room-card">
                        <img src="../<?= htmlspecialchars($room['image_path']) ?>" alt="Room image">

                        <div class="content">
                            <p><strong>Type:</strong> <?= htmlspecialchars($room['room_type']) ?></p>
                            <p><strong>Price:</strong> KES <?= htmlspecialchars($room['price']) ?></p>

                            <?php if ($room['status'] === 'vacant'): ?>
                                <span class="badge vacant">Vacant</span>
                            <?php else: ?>
                                <span class="badge occupied">Occupied</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

            </div>
        <?php endif; ?>
    </section>

</main>

<footer>
    <p>&copy; <?= date("Y") ?> H2P – House Hunting Project</p>
</footer>

</body>
</html>