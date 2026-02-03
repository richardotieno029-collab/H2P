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
$room_number = $_POST['room_number'];
$status    = $_POST['status'];

// 1️⃣ Get old image path
$sql = "SELECT image_path FROM rooms WHERE house_id = ? AND id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $room_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$image_path = $row['image_path'];


// 2️⃣ If new image uploaded
if (!empty($_FILES['room_image']['name'])) {

    $uploadDir = "../uploads/";
    $newImage = time() . "_" . basename($_FILES['room_image']['name']);
    $targetPath = $uploadDir . $newImage;

    if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetPath)) {
        // delete old image
        if (file_exists("" . $image_path)) {
            unlink("" . $image_path);
        }
        $image_path = "../uploads/" . $newImage;
    }
}
// 3️⃣ Update DB
$update = "UPDATE rooms SET 
    room_number = ?,  
    status = ?,  
    image_path = ?
    WHERE house_id = ? AND id = ?";

$stmt = $conn->prepare($update);
$stmt->bind_param(
    "sssii",
    $room_number,
    $status,
    $image_path,
    $house_id,
    $room_id
);

$stmt->execute();

header("Location: rooms.php?refresh=1&house_id=" . $house_id);
exit;