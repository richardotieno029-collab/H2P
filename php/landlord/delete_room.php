<?php
require_once '../db_connect.php';
require_once '../session.php';


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
    header("Location: rooms.php?house_id=" . $house_id);
} else {
    header("Location: landlord_dashboard.php");
}
exit;