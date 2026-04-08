<?php
require_once "auth_landlord.php";
require_once "../includes/risk_engine.php";
require_once "../includes/image_utils.php";
include "../includes/toast.php";


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

// Optimize uploaded image in-place (resize + compress)
optimizeImageFile($target_file, $target_file, 1200, 70);

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
//gallery images
$uploadDir = "../uploads/";

// Limit gallery images per room to 5 total
$maxGallery = 5;
$existingStmt = $conn->prepare("SELECT COUNT(*) AS total FROM room_images WHERE room_id = ?");
$existingStmt->bind_param("i", $room_id);
$existingStmt->execute();
$existingCount = $existingStmt->get_result()->fetch_assoc()['total'];
$remainingSlots = max(0, $maxGallery - $existingCount);

if ($remainingSlots === 0 && !empty($_FILES['gallery_images']['name'][0])) {
    $_SESSION['toast'] = [
        'type' => 'info',
        'message' => 'Maximum of 5 gallery images reached for this room. Remove existing images before adding more.'
    ];
}

//sanitize and upload each gallery image
if (!empty($_FILES['gallery_images']['name'][0]) && $remainingSlots > 0) {

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $uploadedCount = 0;

    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
        if ($uploadedCount >= $remainingSlots) {
            break;
        }

        $fileSize = $_FILES['gallery_images']['size'][$key] ?? 0;
        if ($fileSize > 5 * 1024 * 1024) {
            continue; // skip oversized file
        }

        if (!isset($_FILES['gallery_images']['name'][$key]) || empty($_FILES['gallery_images']['name'][$key])) {
            continue;
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
            optimizeImageFile($targetPath, $targetPath, 1200, 70);

            $image_path = "../uploads/" . $imageName;

            $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $room_id, $image_path);
            $stmt->execute();

            $uploadedCount++;
        }
    }

    finfo_close($finfo);
}

if ($stmt->execute()) {

//log activity
$user_type = 'landlord';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");
$action = 'CREATE_ROOM';
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
    AND created_at > UTC_TIMESTAMP() - INTERVAL 10 MINUTE
");
$check->bind_param("i", $user_id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if ($count >= 8) {

            // risk score
    $user_type = 'landlord';
    $user_id   = $_SESSION['user_id'];

    addRisk($conn, $user_type, $user_id, 15);

    // Prevent duplicate flags within 10 mins
    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='landlord'
        AND user_id=?
        AND reason='Suspicious rapid landlord activity'
        AND created_at > UTC_TIMESTAMP() - INTERVAL 10 MINUTE
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
    }
}


    if (!isset($_SESSION['toast'])) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Room added successfully.'
        ];
    }
    $_SESSION['token'] = bin2hex(random_bytes(32));
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