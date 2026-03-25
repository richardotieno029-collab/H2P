<?php
require_once 'admin_guard.php';
$landlord_id= (int) $_GET['landlord_id'];
$house_id = (int) $_GET['house_id'];
$action = $_GET['action'];

$newStatus = $action === 'suspend' ? 'suspended' : 'active';

$stmt = $conn->prepare("UPDATE houses SET status=? WHERE house_id=?");
$stmt->bind_param("si", $newStatus, $house_id);
$stmt->execute();

header("Location: admin_houses.php?landlord_id=$landlord_id");
exit;