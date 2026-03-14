<?php
require_once 'admin_guard.php';
require_once '../db_connect.php';

$type = $_GET['type'];
$id = (int) $_GET['id'];
$action = $_GET['action'];

$table = $type === 'landlord' ? 'landlords' : 'students';

$newStatus = $action === 'suspend' ? 'suspended' : 'active';

if ($action === 'suspend') {
    $stmt = $conn->prepare("UPDATE $table SET status=?, risk_score=100 WHERE id=?");
    $stmt->bind_param("si", $newStatus, $id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("UPDATE $table SET status=? WHERE id=?");
    $stmt->bind_param("si", $newStatus, $id);
    $stmt->execute();
}

header("Location: users.php?type=$type");
exit;