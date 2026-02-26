<?php
require_once '../db_connect.php';
require_once '../session.php';
include "../toast.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord'){
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'For that you need to be logged in.'
];
    header("Location: login_form.php");
    exit;

}

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
    $_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Room deleted successfully.'
];
    header("Location: landlord_dashboard.php");
}
exit;