<?php
session_start();
require_once "../db_connect.php";

if (!isset($_SESSION['landlord_id'])) {
    header("Location: ../../public/landlord/landlord_login.html");
    exit;
}

$landlord_id = $_SESSION['landlord_id'];

$house_id = intval($_POST['house_id']);
$house_name = $_POST['house_name'];
$area = $_POST['area'];
$room_type = $_POST['room_type'];
$price = $_POST['price'];
$description = $_POST['description'];


// 1️⃣ Get old image path
$sql = "SELECT image_path FROM houses WHERE house_id = ? AND landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$image_path = $row['image_path'];


// 2️⃣ If new image uploaded
if (!empty($_FILES['house_image']['name'])) {

    $uploadDir = "../../uploads/";
    $newImage = time() . "_" . basename($_FILES['house_image']['name']);
    $targetPath = $uploadDir . $newImage;

    if (move_uploaded_file($_FILES['house_image']['tmp_name'], $targetPath)) {
        // delete old image
        if (file_exists("" . $image_path)) {
            unlink("" . $image_path);
        }
        $image_path = "../../uploads/" . $newImage;
    }
}


// 3️⃣ Update DB
$update = "UPDATE houses SET 
    house_name = ?, 
    area = ?, 
    room_type = ?, 
    price = ?, 
    description = ?, 
    image_path = ?
    WHERE house_id = ? AND landlord_id = ?";

$stmt = $conn->prepare($update);
$stmt->bind_param(
    "sssissii",
    $house_name,
    $area,
    $room_type,
    $price,
    $description,
    $image_path,
    $house_id,
    $landlord_id
);

$stmt->execute();

header("Location: landlord_dashboard.php");
exit;