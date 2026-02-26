<?php
session_start();
require_once "../db_connect.php";
include "../toast.php";

$student_id = $_SESSION['user_id'];
$room_id = intval($_POST['room_id']);

$check = $conn->prepare(
    "SELECT fav_id FROM favourites WHERE student_id=? AND room_id=?"
);
$check->bind_param("si", $student_id, $room_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    $conn->query(
        "DELETE FROM favourites WHERE student_id='$student_id' AND room_id=$room_id"
    );
     $_SESSION['toast'] = [
            'type' => 'info',
            'message' => 'Favourites updated.'
        ];
    echo json_encode(['status' => 'removed']);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO favourites (student_id, room_id) VALUES (?, ?)"
    );
     $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Favourites updated.'
        ];
    $stmt->bind_param("si", $student_id, $room_id);
    $stmt->execute();
    echo json_encode(['status' => 'added']);
}
$conn->close();
?>