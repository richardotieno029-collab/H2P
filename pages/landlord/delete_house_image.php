<?php
require_once "auth_landlord.php";

$id = $_GET['id'];
$house_id = $_GET['house_id'];

// Get image path first
$stmt = $conn->prepare("SELECT image_path FROM house_images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();

// Delete file
if(file_exists($image['image_path'])){
    unlink($image['image_path']);
}

// Delete DB record
$stmt = $conn->prepare("DELETE FROM house_images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: house_details.php?id=" . $house_id);
exit();
