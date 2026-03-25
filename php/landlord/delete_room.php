<?php
require_once 'auth_landlord.php';
require_once "../includes/risk_engine.php";
session_start();
include "../toast.php";

$room_id = intval($_GET['id']);

// Optional safety: ensure landlord owns the room
$sql = "
    DELETE r FROM rooms r
    JOIN houses h ON r.house_id = h.house_id
    WHERE r.id = ? AND h.landlord_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $room_id, $_SESSION['user_id']);
$stmt->execute();

$house_id = $_GET['house_id'] ?? null;

if ($house_id) {
     $_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Room deleted successfully.'
];
    header("Location: rooms.php?house_id=" . $house_id);
} else {

//log activity
$user_type = 'landlord';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");
$action = 'DELETE_ROOM';
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
    }
}



    $_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Room deleted successfully.'
];
    header("Location: landlord_dashboard.php");
}
exit;