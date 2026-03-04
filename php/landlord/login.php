<?php
session_start();
require_once "../db_connect.php";
require_once "../includes/risk_engine.php";
include "../toast.php";


/* 1. Ensure form was submitted */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_form.php");
    exit;
}

/* 2. Get & sanitize inputs */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
}
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
/* 3. Basic validation */
if (empty($email) || empty($password)) {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Please enter both fields.'
];
    header("Location: login_form.php");
    exit;
}
// Ignore this part.
$stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($admin && password_verify($password, $admin['password'])) {

    $_SESSION['user_id'] = $admin['admin_id'];
    $_SESSION['user_role'] = 'admin';
    header("Location: ../admin/dashboard.php");
    exit;
}

/* 4. Fetch landlord by email */
$sql = "SELECT * FROM landlords WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

/* 5. Check user exists */
if ($result->num_rows !== 1) {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Invalid email or password.'
];
    header("Location: login_form.php");
    exit;
}

$row = $result->fetch_assoc();

/* 6. Verify password */
if (!password_verify($password, $row['password'])) {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Wrong password.'
];
//log failed attempts
$attempt = $conn->prepare("
    INSERT INTO login_attempts (user_type, user_id, ip_address, success)
    VALUES (?, ?, ?, 0)
");
$type = 'landlord';
$attempt->bind_param("sis", $type, $row['id'], $ip);
$attempt->execute();
       // risk score
    $user_type = 'landlord';
    $user_id   = $row['id'];

    addRisk($conn, $user_type, $user_id, 5);
//flag for too many failed attempts
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

if ($count >= 2) {
            // Prevent duplicate flags within 10 mins
    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='landlord'
        AND user_id=?
        AND reason='Multiple failed login attempts from same IP'
        AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {
    // insert spam flag
    $flag = $conn->prepare("
        INSERT INTO spam_flags (user_type, user_id, reason, severity)
        VALUES ('landlord', ?, 'Multiple failed login attempts from same IP', 'medium')
    ");
    $flag->bind_param("i", $row['id']);
    $flag->execute();

            // risk score
    $user_type = 'landlord';
    $user_id   = $row['id'];

    addRisk($conn, $user_type, $user_id, 15);
    
    }
}
    header("Location: login_form.php");
    exit;
}

//check for suspension
    //risk recalculation
        $user_type = 'landlord';
    $user_id   = $row['id'];

    addRisk($conn, $user_type, $user_id, 0);
if ($row['status'] !== 'active') {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Your account has been suspended. Please contact support.'
];
    header("Location: login_form.php");
    exit;
}

/* 7. Login successful → create session */
$_SESSION['user_id'] = $row['id'];
$_SESSION['landlord_email'] = $row['email'];
$_SESSION['user_name'] = $row['full_name'];
$_SESSION['user_role'] = 'landlord';
$_SESSION['profile_image'] = $row['profile_image'];


/* 8. Redirect to dashboard */
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Login successfull.'
];
header("Location: landlord_dashboard.php");
exit;