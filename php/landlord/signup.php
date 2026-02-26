<?php
session_start();
require_once "../db_connect.php";
include "../toast.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signupform.php");
    exit;
}

/* BASIC INPUT */
if (!hash_equals($_SESSION['tocken'], $_POST['token'])) {
    die("Invalid request.");
}
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $phone === '' || $password === '') {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'All fields except profile picture are required.'
    ];
    header("Location: signup_form.php");
    exit;
}

/* CHECK EMAIL */
$check = $conn->prepare("SELECT landlord_id FROM landlords WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Email already registered.'
    ];
    header("Location: signup_form.php");
    exit;
}

/* PASSWORD */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

/* IMAGE UPLOAD */
$profileImagePath = "../uploads/profiles/default.png"; // fallback

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

    $uploadDir = "../uploads/profiles/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['profile_pic']['tmp_name'];
    $originalName = $_FILES['profile_pic']['name'];
    $fileSize = $_FILES['profile_pic']['size'];


    $newName = "user_" . time() . "_" . random_int(1000, 9999) . "." . $ext;
    $destination = $uploadDir . $newName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Failed to upload image.'
        ];
        header("Location: signup_form.php");
        exit;
    }

    $profileImagePath = "../uploads/profiles/" . $newName;
}

/* INSERT */
$stmt = $conn->prepare(
    "INSERT INTO landlords (full_name, email, phone, profile_image, password)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "sssss",
    $name,
    $email,
    $phone,
    $profileImagePath,
    $hashed_password
);

if ($stmt->execute()) {
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Account created successfully. Please login.'
    ];
    header("Location: login.php");
    exit;
}

$_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Failed to create account.'
];
header("Location: signupform.php");
exit;