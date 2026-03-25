<?php
require_once "auth_landlord.php";

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
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include "../toast.php"; ?>
<div class="form-wrapper">
    <a href="rooms.php?house_id=<?php echo $house_id; ?>" class="back-btn" title="Go back">
    ←
</a>
    <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
<h2>Add Room</h2>

<form action="add_room.php" method="POST" enctype="multipart/form-data" onsubmit="return handleSubmit(this, 'Adding room...')">

    <!-- Link room to house -->
     <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <input type="hidden" name="house_id" value="<?php echo $house_id; ?>">

    <label>Room Number</label><br>
    <input type="text" name="room_number" required>
    <br><br>

    <label>Status</label><br>
    <select name="status" required>
        <option value="vacant">Vacant</option>
        <option value="occupied">Occupied</option>
    </select>
    <br><br>


    <label>Room Cover Image</label><br>
    <input type="file" name="room_image" accept="image/*" data-max-size="5242880" required>
    <br><br>

    <label>Upload other Images</label>
        <input type="file" name="gallery_images[]" accept="image/*" data-max-files="5" data-max-size="5242880" multiple required>

    <button type="submit">Add Room</button>

</form>
</div>


</body>
</html>