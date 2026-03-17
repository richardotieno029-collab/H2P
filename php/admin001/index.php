<?php
require_once 'admin001_guard.php';
require_once '../db_connect.php';

// Quick stats for super admin
$totalLandlords = $conn->query("SELECT COUNT(*) as total FROM landlords")->fetch_assoc()['total'];
$totalStudents  = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$totalHouses    = $conn->query("SELECT COUNT(*) as total FROM houses")->fetch_assoc()['total'];
$totalRooms     = $conn->query("SELECT COUNT(*) as total FROM rooms")->fetch_assoc()['total'];
$totalAdmins    = $conn->query("SELECT COUNT(*) as total FROM admins")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 Control Panel</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<a href="../admin/dashboard.php" class="back-btn" title="Go back">
    Back to Admin Dashboard
</a>

<div class="admin-wrapper">
    <h1><i class="fa-solid fa-shield-halved"></i> Admin001 Control Panel</h1>

    <div class="cards">
        <div class="card">
            <i class="fa-solid fa-users"></i>
            <h3><?= $totalLandlords ?></h3>
            <p>Landlords</p>
            <a href="users.php?type=landlord">Manage</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-user-graduate"></i>
            <h3><?= $totalStudents ?></h3>
            <p>Students</p>
            <a href="users.php?type=student">Manage</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-building"></i>
            <h3><?= $totalHouses ?></h3>
            <p>Houses</p>
            <a href="houses.php">Manage</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-door-open"></i>
            <h3><?= $totalRooms ?></h3>
            <p>Rooms</p>
            <a href="rooms.php">Manage</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-hammer"></i>
            <h3><?= $totalAdmins ?></h3>
            <p>Admins</p>
            <a href="manage_admins.php">Manage</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-book"></i>
            <h3>Logs</h3>
            <p>Activity Logs</p>
            <a href="logs.php">View</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-list"></i>
            <h3>Bookings</h3>
            <p>Room Requests</p>
            <a href="bookings.php">View</a>
        </div>

        <div class="card">
            <i class="fa-solid fa-user-friends"></i>
            <h3>Roommate Requests</h3>
            <p>View and manage</p>
            <a href="roommate_requests.php">View</a>
        </div>

    </div>
</div>

</body>
</html>
