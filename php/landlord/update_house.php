<?php
require_once "auth_landlord.php";
require_once "../db_connect.php";
require_once "../includes/risk_engine.php";
session_start();
include "../toast.php";

$landlord_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
}
$house_id = intval($_POST['house_id']);
$house_name = $_POST['house_name'];
$area = $_POST['area'];
$room_type = $_POST['room_type'];
$price = $_POST['price'];
$description = $_POST['description'];
// UTILITIES (Checkbox Handling)
$electricity_available = isset($_POST['electricity_available']) ? 1 : 0;
$electricity_covered = isset($_POST['electricity_covered']) ? 1 : 0;
$token_meter = isset($_POST['token_meter']) ? 1 : 0;

$water_available = isset($_POST['water_available']) ? 1 : 0;
$water_covered = isset($_POST['water_covered']) ? 1 : 0;

$wifi_available = isset($_POST['wifi_available']) ? 1 : 0;
$wifi_extra_payment = isset($_POST['wifi_extra_payment']) ? 1 : 0;

$hot_shower = isset($_POST['hot_shower']) ? 1 : 0;
$shared_toilet = isset($_POST['shared_toilet']) ? 1 : 0;
$shared_water_point = isset($_POST['shared_water_point']) ? 1 : 0;
if ($electricity_available == 0) {
    $electricity_covered = 0;
    $token_meter = 0;
}

if ($water_available == 0) {
    $water_covered = 0;
}

if ($wifi_available == 0) {
    $wifi_extra_payment = 0;
}


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
    //sanitize image upload
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if (!isset($_FILES['house_image']) || $_FILES['house_image']['error'] !== 0) {
    die("No image uploaded.");
}
if ($_FILES['house_image']['size'] > 5 * 1024 * 1024) {
    die("File too large.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['house_image']['tmp_name']);

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mime, $allowed_types)) {
    die("Invalid file type.");
}

    $uploadDir = "../uploads/";
    $newImage = time() . "_" . basename($_FILES['house_image']['name']);
    $targetPath = $uploadDir . $newImage;

    if (move_uploaded_file($_FILES['house_image']['tmp_name'], $targetPath)) {
        // delete old image
        if (file_exists("" . $image_path)) {
            unlink("" . $image_path);
        }
        $image_path = "../uploads/" . $newImage;
    }
}


// 3️⃣ Update DB
$update = "UPDATE houses SET 
    house_name = ?, 
    area = ?, 
    room_type = ?, 
    price = ?, 
    description = ?, 
    image_path = ?,
 electricity_available = ?, electricity_covered = ?, token_meter = ?,
 water_available = ?, water_covered = ?,
 wifi_available = ?, wifi_extra_payment = ?,
 hot_shower = ?, shared_toilet = ?, shared_water_point = ?
    WHERE house_id = ? AND landlord_id = ?";

$stmt = $conn->prepare($update);
$stmt->bind_param(
    "sssissiiiiiiiiiiii",
    $house_name,
    $area,
    $room_type,
    $price,
    $description,
    $image_path,
    $electricity_available, $electricity_covered, $token_meter,
    $water_available, $water_covered,
    $wifi_available, $wifi_extra_payment,
    $hot_shower, $shared_toilet, $shared_water_point,
    $house_id,
    $landlord_id
);
//gallery images
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

            $stmt = $conn->prepare("INSERT INTO house_images (house_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $house_id, $image_path);
            $stmt->execute();
        }
    }

    finfo_close($finfo);
}

$stmt->execute();

//log activity
$user_type = 'landlord';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");
$action = 'UPDATE_HOUSE';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();
//spam check
// Rapid action detection (Landlord)

$check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM activity_logs
    WHERE user_type='landlord'
    AND user_id=?
    AND action IN (
        'CREATE_HOUSE',
        'UPDATE_HOUSE',
        'DELETE_HOUSE',
        'CREATE_ROOM',
        'UPDATE_ROOM',
        'DELETE_ROOM'
    )
    AND created_at > NOW() - INTERVAL 10 MINUTE
");
$check->bind_param("i", $user_id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if ($count >= 8) {

    // Prevent duplicate flags within 10 mins
    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='landlord'
        AND user_id=?
        AND reason='Suspicious rapid landlord activity'
        AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {

        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('landlord', ?, 'Suspicious rapid landlord activity', 'medium')
        ");
        $flag->bind_param("i", $user_id);
        $flag->execute();

    //risk score
addRisk($conn, $user_type, $user_id, 5);
    }
}



 $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'House updated successfully.'
        ];
header("Location: landlord_dashboard.php");
exit;