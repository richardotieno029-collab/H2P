<?php
session_start();
require_once "../db_connect.php";
require_once "../includes/risk_engine.php";
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
$ip = $_SERVER['REMOTE_ADDR'];
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
    (student_id, full_name, email, phone, password, profile_image, ip_address)
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssssss",
    $student_id,
    $full_name,
    $email,
    $phone,
    $hashedPassword,
    $profileImagePath,
    $ip
);

if ($stmt->execute()) {

//log activity
$user_type = 'student';
$user_id   = $_SESSION['user_id'];
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");
$action = 'CREATE_ACCOUNT';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();
//too many users on same ip
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM students 
    WHERE ip_address=? 
    AND created_at > NOW() - INTERVAL 1 HOUR
");
$stmt->bind_param("s", $ip);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

if ($count >= 3) {

//prevent duplicate flags
    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='student'
        AND user_id=?
        AND reason='Multiple registrations from same IP'
        AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {

    $flag = $conn->prepare("
        INSERT INTO spam_flags (user_type, user_id, reason, severity)
        VALUES ('student', ?, 'Multiple registrations from same IP', 'high')
    ");
    $flag->bind_param("i", $user_id);
    $flag->execute();
    
         // risk score
    $user_type = 'student';
    $user_id   = $row['id'];

    addRisk($conn, $user_type, $user_id, 5);
    }
}

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