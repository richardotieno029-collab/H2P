<?php
session_start();
include "../db_connect.php";

if ($_SESSION['user_role'] !== 'landlord') {
    header("Location: ../index/index.php");
    exit;

}

/* Validate POST */
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request.");
}

$house_id    = (int)$_POST['house_id'];
$room_type   = $_POST['room_type'];
$price       = $_POST['price'];
$status      = $_POST['status'];
$description = $_POST['description'];

/* IMAGE UPLOAD */
$upload_dir = "../../uploads/";

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
$image_path = "uploads/" . $image_name;

/* Insert room */
$sql = "INSERT INTO rooms 
        (house_id, room_type, price, status, description, image_path)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isisss",
    $house_id,
    $room_type,
    $price,
    $status,
    $description,
    $image_path
);

if ($stmt->execute()) {
    header("Location: rooms.php?refresh=1&house_id=" . $house_id);
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();