<?php
session_start();
require_once "../includes/config/db_connect.php";
require_once "../includes/risk_engine.php";
require_once "../includes/phone_utils.php";
require_once "../includes/image_utils.php";
include "../toast.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup_form.php");
    exit;
}

/* CSRF TOKEN CHECK */
if (!isset($_POST['token']) || 
    !isset($_SESSION['token']) || 
    !hash_equals($_SESSION['token'], $_POST['token'])) {
    die("Invalid request.");
}

$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$full_name  = trim($_POST['name']);
$email      = trim($_POST['email']);
$phone      = trim($_POST['phone']);
$password   = $_POST['password'];

/* REQUIRED FIELDS */
if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'All fields are required.'
    ];
    header("Location: signup_form.php");
    exit;
}

/* CHECK DUPLICATE EMAIL */
$check = $conn->prepare(
    "SELECT id FROM landlords WHERE email = ?"
);
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

/* PHONE FORMAT VALIDATION */
$phonePattern = '/^(?:07|01)\d{8}$|^(?:2547|2541)\d{8}$/';
if (!preg_match($phonePattern, $phone)) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Enter a valid phone number (e.g. 0712345678, 0112345678, 254712345678 or 254112345678).'
    ];
    header("Location: signup_form.php");
    exit;
}

/* Normalize phone so 2547... and 07... are treated as the same number */
$normalizedPhone = normalizePhoneForDb($phone);
$phoneVariants = getPhoneVariants($phone);

/* CHECK DUPLICATE PHONE */
$placeholders = implode(',', array_fill(0, count($phoneVariants), '?'));
$checkPhoneSql = "SELECT id FROM landlords WHERE phone IN ($placeholders)";
$checkPhone = $conn->prepare($checkPhoneSql);
$types = str_repeat('s', count($phoneVariants));
$checkPhone->bind_param($types, ...$phoneVariants);
$checkPhone->execute();
$checkPhone->store_result();

if ($checkPhone->num_rows > 0) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Phone number already registered.'
    ];
    header("Location: signup_form.php");
    exit;
}
    // 2️⃣ Strong password validation
    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Za-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Password must be at least 8 characters and contain letters and numbers.'
        ];
            header("Location: signup_form.php");
    exit;
    }

/* HASH PASSWORD */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* GENERATE EMAIL TOKEN */
$token = bin2hex(random_bytes(32));

/* PROFILE IMAGE */
$profileImagePath = null;

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {

    if ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) {
        die("File too large.");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);

    $allowed = ['image/jpeg','image/png','image/gif'];

    if (!in_array($mime, $allowed)) {
        die("Invalid file type.");
    }

    $uploadDir = "../uploads/profiles/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir,0777,true);
    }

    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);

    $newName = "student_" . time() . "_" . random_int(1000,9999) . "." . $ext;

    $destination = $uploadDir . $newName;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
        optimizeImageFile($destination, $destination, 1200, 70);
        $profileImagePath = $destination;
    }
}

/* INSERT LANDLORD */
$stmt = $conn->prepare("
INSERT INTO landlords
(full_name,email,phone,password,profile_image,verification_token,ip_address,user_agent,
email_verified,token_expires,status)
VALUES (?,?,?,?,?,?,?,?,0,DATE_ADD(NOW(), INTERVAL 1 HOUR),'active')
");

$stmt->bind_param(
"ssssssss",
$full_name,
$email,
$normalizedPhone,
$hashedPassword,
$profileImagePath,
$token,
$ip,
$user_agent
);

if ($stmt->execute()) {

$landlord_id = $conn->insert_id;

/* LOG ACTIVITY */
$action = 'CREATE_ACCOUNT';

$log = $conn->prepare("
INSERT INTO activity_logs
(user_type,user_id,action,ip_address)
VALUES ('landlord',?,?,?)
");

$log->bind_param("iss",$landlord_id,$action,$ip);
$log->execute();



/* Email verification */
require_once "../includes/mailer.php";
$verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/H2P/php/landlord/verify_email.php?token=" . $token;
$subject = "Verify Your H2P Account";
$body = "Hi $full_name,<br><br>Please click the link below to verify your email:<br>
<a href='$verificationLink'>Verify Account</a><br><br>This link will expire in 1 hour.<br><br>Ignore this email if you didn't sign up.<br><br>
Best,<br>H2P Team";

$mailResult = sendMail($email, $full_name, $subject, $body);
if ($mailResult !== true) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Signup succeeded but verification email failed: ' . $mailResult
    ];
    header("Location: signup_form.php");
    exit;
}

$_SESSION['toast'] = [
'type'=>'success',
'message'=>'Account created. Check your email to verify before login.'
];

header("Location: verify_notice.php?email=".$email);

} else {

$_SESSION['toast'] = [
'type'=>'error',
'message'=>'Signup failed.'
];

header("Location: signup_form.php");

}