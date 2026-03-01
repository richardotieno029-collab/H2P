<?php
require_once "auth_landlord.php";
require_once "../db_connect.php";

$id = $_GET['id'];
$room_id = $_GET['room_id'];

// Get image path first
$stmt = $conn->prepare("SELECT image_path FROM room_images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();

// Delete file
if(file_exists($image['image_path'])){
    unlink($image['image_path']);
}

// Delete DB record
$stmt = $conn->prepare("DELETE FROM room_images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: room_details.php?id=" . $room_id);
exit();
