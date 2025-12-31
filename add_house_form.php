<?php
session_start();
if (!isset($_SESSION['landlord_id'])) {
    header("Location: landlord_login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add House | H2P</title>
    <link rel="stylesheet" href="../../public/assets/styles.css">
</head>
<body>

<div class="form-wrapper">
    <h2>Add a New House</h2>

<form action="add_house.php" method="POST" enctype="multipart/form-data">


        <label>House Name</label>
        <input type="text" name="house_name" required>

        <label>Area</label>
        <select name="area" required>
            <option>Kangaru</option>
            <option>Gakwegori</option>
            <option>Spring Valley</option>
            <option>Kayole</option>
            <option>Kamiu</option>
            <option>Bagik</option>
            <option>Njukiri</option>
            <option>Leaders</option>
        </select>

        <label>House Type</label>
        <select name="room_type" required>
            <option>Single</option>
            <option>Bedsitter</option>
            <option>One Bedroom</option>
            <option>Two Bedroom</option>
        </select>

        <label>Price (KES)</label>
        <input type="number" name="price" required>

        <label>Upload Image</label>
        <input type="file" name="house_image" required>

<label>Description</label>
        <input type="text" name="description" required>

        <button type="submit">Save House</button>

    </form>

</div>

</body>
</html>