<?php
session_start();
require_once "../db_connect.php";
include "../toast.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup_form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
}

$student_id = trim($_POST['student_id']);
$full_name  = trim($_POST['full_name']);
$email      = trim($_POST['email']);
$phone      = trim($_POST['phone']);
$password   = $_POST['password'];

if (empty($student_id) || empty($full_name) || empty($email) || empty($phone) || empty($password)) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'All fields except profile picture are required.'
    ];
    header("Location: signup_form.php");
    exit;
}

/* 🔍 Check duplicates */
$check = $conn->prepare(
    "SELECT student_id FROM students WHERE student_id = ? OR email = ?"
);
$check->bind_param("ss", $student_id, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Student ID or Email already exists.'
    ];
    header("Location: signup_form.php");
    exit;
}

/* 🔐 Hash password */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* 🖼 Profile image upload */
$profileImagePath = null;

if (
    isset($_FILES['profile_pic']) &&
    $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK
) {

    //sanitize image upload
$finfo = finfo_open(FILEINFO_MIME_TYPE);
if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== 0) {
    die("No image uploaded.");
}
if ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) {
    die("File too large.");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mime, $allowed_types)) {
    die("Invalid file type.");
}
    $uploadDir = "../uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['profile_pic']['tmp_name'];
    $originalName = $_FILES['profile_pic']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));


    $newName = "student_" . time() . "_" . random_int(1000, 9999) . "." . $ext;
    $destination = $uploadDir . $newName;

    move_uploaded_file($tmpName, $destination);
    $profileImagePath = $destination;
}

/* 🧾 Insert student */
$stmt = $conn->prepare(
    "INSERT INTO students 
    (student_id, full_name, email, phone, password, profile_image)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssssss",
    $student_id,
    $full_name,
    $email,
    $phone,
    $hashedPassword,
    $profileImagePath
);

$_SESSION['token'] = bin2hex(random_bytes(32));

if ($stmt->execute()) {
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Signup successful. Please login.'
    ];
    $_SESSION['token'] = bin2hex(random_bytes(32));
    header("Location: login_form.php");
} else {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Signup failed. Try again.'
    ];
    header("Location: signup_form.php");
}