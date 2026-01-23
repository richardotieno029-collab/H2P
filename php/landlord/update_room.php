<?php
require_once '../db_connect.php';
require_once '../session.php';

$room_id   = intval($_POST['room_id']);
$house_id  = intval($_POST['house_id']);
$type      = $_POST['room_type'];
$price     = $_POST['price'];
$status    = $_POST['status'];
$desc      = $_POST['description'];

$image_sql = "";
$params = [$type, $price, $status, $desc, $room_id, $_SESSION['user_id']];
$types  = "sissii";

// 1️⃣ Get old image path
$sql = "SELECT image_path FROM rooms WHERE id = ? AND house_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$image_path = $row['image_path'];


// 2️⃣ If new image uploaded
if (!empty($_FILES['room_image']['name'])) {

    $uploadDir = "../../uploads/";
    $newImage = time() . "_" . basename($_FILES['room_image']['name']);
    $targetPath = $uploadDir . $newImage;

    if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetPath)) {
        // delete old image
        if (file_exists("" . $image_path)) {
            unlink("" . $image_path);
        }
        $image_path = "../../uploads/" . $newImage;
    }
}

$sql = "
    UPDATE rooms r
    JOIN houses h ON r.house_id = h.house_id
    SET r.room_type = ?, r.price = ?, r.status = ?, r.description = ? $image_sql
    WHERE r.id = ? AND h.landlord_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

header("Location: rooms.php?house_id=" . $house_id);
exit;
