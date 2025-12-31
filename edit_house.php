<?php
session_start();
require_once "../db_connect.php";

if (!isset($_SESSION['landlord_id'])) {
    header("Location: ../../public/landlord/landlord_login.html");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: landlord_dashboard.php");
    exit;
}

$house_id = intval($_GET['id']);
$landlord_id = $_SESSION['landlord_id'];

$sql = "SELECT * FROM houses WHERE house_id = ? AND landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: landlord_dashboard.php");
    exit;
}

$house = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit House</title>
    <link rel="stylesheet" href="../../public/assets/styles.css">
</head>
<body>

<div class="form-wrapper">
<h2>Edit House</h2>

<form action="update_house.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="house_id" value="<?php echo $house['house_id']; ?>">

    <label>House Name</label>
    <input type="text" name="house_name" value="<?php echo htmlspecialchars($house['house_name']); ?>" required>

    <label>Area</label>
    <select name="area" value="<?php echo htmlspecialchars($house['area']); ?>" required>
        <option>Kangaru</option>
            <option>Gakwegori</option>
            <option>Spring Valley</option>
            <option>Kayole</option>
            <option>Kamiu</option>
            <option>Bagik</option>
            <option>Njukiri</option>
            <option>Leaders</option>
        </select>

    <label>Room Type</label>
    <select name="room_type" value="<?php echo htmlspecialchars($house['room_type']); ?>" required>
        <option>Single</option>
        <option>Bedsitter</option>
        <option>One Bedroom</option>
        <option>Two Bedroom</option>
        </select>

    <label>Price</label>
    <input type="number" name="price" value="<?php echo $house['price']; ?>" required>

    <label>Description</label>
    <input type="text" name="description" value="<?php echo htmlspecialchars($house['description']); ?>" required>

    <p>Current Image:</p>
    <img src="<?php echo $house['image_path']; ?>" width="150">

    <label>Change Image (optional)</label>
    <input type="file" name="house_image">

    <button type="submit">Update House</button>
</form>

</div>
</body>
</html>