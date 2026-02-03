<?php
session_start();
require_once "../db_connect.php";

if ($_SESSION['user_role'] !== 'landlord') {
    header("Location: ../index/index.php");
    exit;
}

$landlord_id = $_SESSION['user_id'];

$room_id   = (int) $_POST['id'];
$house_id  = (int) $_POST['house_id'];
$room_number      = $_POST['room_number'];
$status    = $_POST['status'];

/* IMAGE UPLOAD */
$upload_dir = "../uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$image_name = time() . "_" . basename($_FILES["image"]["name"]);
$target_file = $upload_dir . $image_name;

/* Basic image validation */
$image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$allowed = ["jpg", "jpeg", "png", "gif"];

if (!in_array($image_type, $allowed)) {
    die("Only JPG, JPEG, PNG, GIF allowed.");
}

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    die("Image upload failed.");
}

/* Store RELATIVE path */
$image_path = "../uploads/" . $image_name;

// 3️⃣ Update DB
$update = "UPDATE rooms SET 
    room_type = ?, 
    price = ?, 
    status = ?, 
    description = ?, 
    image_path = ?
    WHERE house_id = ? AND id = ?";

$stmt = $conn->prepare($update);
$stmt->bind_param(
    "sdsssii",
    $type,
    $price,
    $status,
    $desc,
    $image_path,
    $house_id,
    $room_id
);

$stmt->execute();

header("Location: rooms.php?refresh=1&house_id=" . $house_id);
exit;