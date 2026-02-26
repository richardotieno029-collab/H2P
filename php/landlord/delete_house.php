<?php
require_once "../session.php";
require_once "../db_connect.php";
include "../toast.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord'){
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'For that you need to be logged in.'
];
    header("Location: login_form.php");
    exit;

}

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


// 4️⃣ Redirect back
 $_SESSION['toast'] = [
    'type' => 'succes',
    'message' => 'House deleted successfully.'
];
header("Location: landlord_dashboard.php");
exit;