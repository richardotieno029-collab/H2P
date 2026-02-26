<?php
require_once "../session.php";
include "../db_connect.php";
include "../toast.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord'){
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'For that you need to be logged in.'
];
    header("Location: login_form.php");
    exit;

}

/* Validate POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
}
$house_id    = (int)$_POST['house_id'];
$room_number   = $_POST['room_number'];
$status      = $_POST['status'];
/* IMAGE UPLOAD */
$upload_dir = "../uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$image_name = time() . "_" . basename($_FILES["room_image"]["name"]);
$target_file = $upload_dir . $image_name;

//sanitize image upload
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if (!isset($_FILES['room_image']) || $_FILES['room_image']['error'] !== 0) {
    die("No image uploaded.");
}
if ($_FILES['room_image']['size'] > 5 * 1024 * 1024) {
    die("File too large.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['room_image']['tmp_name']);

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mime, $allowed_types)) {
    die("Invalid file type.");
}


if (!move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
    die("Image upload failed.");
}

/* Store RELATIVE path */
$image_path = "../uploads/" . $image_name;

/* Insert room */
$sql = "INSERT INTO rooms 
        (house_id, room_number, status, image_path)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isss",
    $house_id,
    $room_number,
    $status,
    $image_path
);
$stmt->execute();
$room_id = $conn->insert_id;
//gallery
$uploadDir = "../uploads/";

//sanitize and upload each gallery image
if (!empty($_FILES['gallery_images']['name'][0])) {

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['house_image']['size'] > 5 * 1024 * 1024) {
    die("File too large.");
}

        if ($_FILES['gallery_images']['error'][$key] !== 0) {
            continue;
        }

        $mime = finfo_file($finfo, $tmp_name);

        if (!in_array($mime, $allowed_types)) {
            continue; // skip invalid file
        }

        $imageName = time() . "_" . basename($_FILES['gallery_images']['name'][$key]);
        $targetPath = $uploadDir . $imageName;

        if (move_uploaded_file($tmp_name, $targetPath)) {

            $image_path = "../uploads/" . $imageName;

            $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $room_id, $image_path);
            $stmt->execute();
        }
    }

    finfo_close($finfo);
}

if ($stmt->execute()) {
    $_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Room added successfully.'
];
    header("Location: rooms.php?refresh=1&house_id=" . $house_id);
    exit();
} else {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Failed to add room. Please try again.'
];
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();