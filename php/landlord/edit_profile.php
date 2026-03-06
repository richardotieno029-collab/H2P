<?php
require_once "auth_landlord.php";
require_once "../db_connect.php";

/* 1. Ensure form was submitted */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: edit_profile_form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
}
$id = $_SESSION['user_id'];

$full_name  = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);

/* 1️⃣ Get old image */
$stmt = $conn->prepare("
    SELECT profile_image FROM landlords WHERE id = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();
$profile_image = $old['profile_image'];

/* 2️⃣ Handle new image (if uploaded) */
if (!empty($_FILES['profile_image']['name'])) {

    //sanitize image upload
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== 0) {
    die("No image uploaded.");
}
if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
    die("File too large.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mime, $allowed_types)) {
    die("Invalid file type.");
}

    $uploadDir = "../uploads/profiles/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newImage = time() . "_" . basename($_FILES['profile_image']['name']);
    $targetPath = $uploadDir . $newImage;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {


        // delete old image if exists
        if ($profile_image && file_exists("" . $profile_image)) {
            unlink("" . $profile_image);
        }

        $profile_image = "../uploads/profiles/" . $newImage;
    }
}
  $_SESSION['token'] = bin2hex(random_bytes(32));

/* 3️⃣ Update profile */
$update = $conn->prepare("
    UPDATE landlords 
    SET full_name = ?, email = ?, phone = ?, profile_image = ?
    WHERE id = ?
");
$update->bind_param(
    "sssss",
    $full_name,
    $email,
    $phone,
    $profile_image,
    $id
);
$update->execute();

/* 4️⃣ Redirect with success */
 $_SESSION['toast'] = [
            "message" => "Profile updated successfully.",
            "type" => "success"
        ];
header("Location: edit_profile_form.php?success=1");
exit;