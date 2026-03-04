<?php
require_once "auth_landlord.php";
require_once "../db_connect.php";
require_once "../includes/risk_engine.php";
session_start();
include "../toast.php";


if (!isset($_GET['id'])) {
     $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Identity Error!.'
];
    header("Location: landlord_dashboard.php");
    exit;
}

$house_id = intval($_GET['id']);
$landlord_id = $_SESSION['user_id'];


// 1️⃣ Get image path (to delete file)
$sql = "SELECT image_path FROM houses WHERE house_id = ? AND landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // house not found or not owned by landlord
     $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'You are not authorized to access this page.'
];
    header("Location: landlord_dashboard.php");
    exit;
}

$row = $result->fetch_assoc();
$image_path = "" . $row['image_path'];


// 2️⃣ Delete DB record
$delete = "DELETE FROM houses WHERE house_id = ? AND landlord_id = ?";
$stmt = $conn->prepare($delete);
$stmt->bind_param("ii", $house_id, $landlord_id);
$stmt->execute();


// 3️⃣ Delete image file (if exists)
if (file_exists($image_path)) {
    unlink($image_path);
}

//log activity
$user_type = 'landlord';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");
$action = 'DELETE_HOUSE';
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

                    // risk score
    $user_type = 'landlord';
    $user_id   = $_SESSION['user_id'];

    addRisk($conn, $user_type, $user_id, 20);
    }
}



// 4️⃣ Redirect back
 $_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'House deleted successfully.'
];
header("Location: landlord_dashboard.php");
exit;