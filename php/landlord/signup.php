<?php
session_start();
require_once "../db_connect.php";
require_once "../includes/risk_engine.php";
include "../toast.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup_form.php");
    exit;
}

/* BASIC INPUT */
if (!hash_equals($_SESSION['tocken'], $_POST['token'])) {
    die("Invalid request.");
}
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
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
$check = $conn->prepare("SELECT id FROM landlords WHERE email = ?");
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
    "INSERT INTO landlords (full_name, email, phone, profile_image, password, ip_address, user_agent)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssssss",
    $name,
    $email,
    $phone,
    $profileImagePath,
    $hashed_password,
    $ip,
    $user_agent
);
if ($stmt->execute()) {

    $landlord_id = $conn->insert_id;

    // Log activity
    $user_type = 'landlord';
    $user_id   = $landlord_id;
    $ip        = $_SERVER['REMOTE_ADDR'];

    $log = $conn->prepare("
        INSERT INTO activity_logs (user_type, user_id, action, ip_address)
        VALUES (?, ?, ?, ?)
    ");
    $action = 'CREATE_ACCOUNT';
    $log->bind_param("siss", $user_type, $user_id, $action, $ip);
    $log->execute();


    // Check multiple accounts from same IP
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM landlords 
    WHERE ip_address=? 
    AND user_agent=? 
    AND created_at > NOW() - INTERVAL 1 HOUR
");
$stmt->bind_param("ss", $ip, $user_agent);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];
    if ($count >= 3) {

        $existing = $conn->prepare("
            SELECT id FROM spam_flags
            WHERE user_type='landlord'
            AND user_id=?
            AND reason='Multiple registrations from same IP'
            AND created_at > NOW() - INTERVAL 10 MINUTE
        ");
        $existing->bind_param("i", $landlord_id);
        $existing->execute();

        if ($existing->get_result()->num_rows == 0) {

            $flag = $conn->prepare("
                INSERT INTO spam_flags (user_type, user_id, reason, severity)
                VALUES ('landlord', ?, 'Multiple registrations from same IP', 'high')
            ");
            $flag->bind_param("i", $landlord_id);
            $flag->execute();

         // risk score
    $user_type = 'landlord';
    $user_id   = $landlord_id;

    addRisk($conn, $user_type, $user_id, 25);
        }
    }
}

$_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Account created successfully. Please login.'
    ];
    header("Location: login.php");
    exit;


$_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Failed to create account.'
];
header("Location: signup_form.php");
exit;