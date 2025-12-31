<?php
session_start();
require_once "../db_connect.php";

if (!isset($_SESSION['landlord_id'])) {
    header("Location: ../../public/landlord/landlord_login.html");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: landlord_dashboard.php");
    exit;
}

$house_id = intval($_GET['id']);
$landlord_id = $_SESSION['landlord_id'];


// 1️⃣ Get image path (to delete file)
$sql = "SELECT image_path FROM houses WHERE house_id = ? AND landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // house not found or not owned by landlord
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


// 4️⃣ Redirect back
header("Location: landlord_dashboard.php");
exit;