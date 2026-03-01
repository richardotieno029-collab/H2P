<?php
require_once '../db_connect.php';
require_once 'auth_landlord.php';
include "../toast.php";

if (!isset($_GET['id'])) {
    die("Room not specified");
}

$room_id = intval($_GET['id']);

$sql = "
    SELECT r.*, h.house_id
    FROM rooms r
    JOIN houses h ON r.house_id = h.house_id
    WHERE r.id = ? AND h.landlord_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$room = $result->fetch_assoc();
if (!$room) {
    die("Room not found or access denied");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Room</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../toast.php"; ?>

<div class="form-wrapper">
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
    <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
<h2>Edit Room</h2>

<form action="edit_room.php" method="POST" enctype="multipart/form-data" class="form">
    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <input type="hidden" name="id" value="<?= $room['id'] ?>">
    <input type="hidden" name="house_id" value="<?= $room['house_id'] ?>">


    <label>Room Number</label>
    <input type="text" name="room_number" value="<?= $room['room_number'] ?>" required>

    <label>Status</label>
    <select name="status">
        <option value="vacant" <?= $room['status'] === 'vacant' ? 'selected' : '' ?>>Vacant</option>
        <option value="occupied" <?= $room['status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option>
    </select>

    <p>Current Image:</p>
    <img src="<?php echo $room['image_path']; ?>" width="150">
    <label>Change Image (optional)</label>
    <input type="file" name="image">

    <label>Add other Images</label>
        <input type="file" name="gallery_images[]" multiple >


    <button type="submit" class="btn">Update Room</button>
</form>
</div>

</body>
</html>