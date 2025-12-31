<?php
session_start();
require_once "../db_connect.php";

if (!isset($_SESSION['landlord_id'])) {
    header("Location: landlord_login.html");
    exit();
}

$landlord_id = $_SESSION['landlord_id'];

$house_name = $_POST['house_name'];
$area = $_POST['area'];
$room_type = $_POST['room_type'];
$price = $_POST['price'];
$description = $_POST['description'];

// IMAGE UPLOAD
$uploadDir = "uploads/"; // adjust path if needed

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imageName = time() . "_" . basename($_FILES['house_image']['name']);
$targetPath = $uploadDir . $imageName;

if (move_uploaded_file($_FILES['house_image']['tmp_name'], $targetPath)) {
    // SUCCESS
    $image_path = "../../uploads/" . $imageName;
} else {
    die("Image upload failed");
}
// INSERT
$sql = "INSERT INTO houses 
(landlord_id, house_name, area, room_type, price, description, image_path)
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssiss",
    $landlord_id,
    $house_name,
    $area,
    $room_type,
    $price,
    $description,
    $image_path
);

$stmt->execute();

header("Location: landlord_dashboard.php");
exit();