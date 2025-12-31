<?php
require_once 'auth_landlord.php';
require_once '../db.php';

$landlord_id = $_SESSION['user_id'];

$room_name = $_POST['room_name'];
$area_id = $_POST['area_id'];
$room_type = $_POST['room_type'];
$price = $_POST['price'];
$description = $_POST['description'];

// IMAGE UPLOAD
$imageName = time() . "_" . $_FILES['image']['name'];
$targetPath = "../uploads/" . $imageName;

move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);

// SAVE TO DB
$stmt = $conn->prepare("
INSERT INTO rooms 
(room_name, area_id, room_type, price, description, image_path, landlord_id)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
  "sissssi",
  $room_name,
  $area_id,
  $room_type,
  $price,
  $description,
  $imageName,
  $landlord_id
);

$stmt->execute();

header("Location: dashboard.php");
exit();