<?php
session_start();
include "../db_connect.php";

/* Protect page */
if (!isset($_SESSION['landlord_id'])) {
    header("Location: ../../public/landlord/landlord_login.html");
    exit();
}

/* Validate house_id */
if (!isset($_GET['house_id'])) {
    die("House not specified.");
}

$house_id = (int)$_GET['house_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/assets/styles.css">
</head>
<body>

<div class="form-wrapper">
<h2>Add Room</h2>

<form action="add_room.php" method="POST" enctype="multipart/form-data">

    <!-- Link room to house -->
    <input type="hidden" name="house_id" value="<?php echo $house_id; ?>">

    <label>Room Type</label><br>
    <select name="room_type" required>
        <option value="">-- Select Room Type --</option>
        <option value="Single">Single</option>
        <option value="Bedsitter">Bedsitter</option>
        <option value="One Bedroom">One Bedroom</option>
        <option value="Two Bedroom">Two Bedroom</option>
    </select>
    <br><br>

    <label>Price (KES)</label><br>
    <input type="number" name="price" required>
    <br><br>

    <label>Status</label><br>
    <select name="status" required>
        <option value="vacant">Vacant</option>
        <option value="occupied">Occupied</option>
    </select>
    <br><br>

    <label>Description</label><br>
    <textarea name="description" rows="4"></textarea>
    <br><br>

    <label>Room Image</label><br>
    <input type="file" name="image" accept="image/*" required>
    <br><br>

    <button type="submit">Add Room</button>

</form>
</div>

<br>
<a href="dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>