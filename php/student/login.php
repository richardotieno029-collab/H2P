<?php
session_start();
require_once "../db_connect.php";
require_once "../includes/risk_engine.php";
include "../toast.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
}
$student_id = trim($_POST['student_id']);
$password   = $_POST['password'];
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare(
    "SELECT id, student_id, full_name, password,status, profile_image 
     FROM students WHERE student_id = ?"
);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {

    //check for suspension
if ($row['status'] !== 'active') {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Your account has been suspended. Please contact support.'
];
    header("Location: login_form.php");
    exit;
}

        /* ✅ Login success */
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['full_name'];
        $_SESSION['profile_image'] = $row['profile_image'];
            $_SESSION['user_role'] = 'student';
            

            $_SESSION['token'] = bin2hex(random_bytes(32));

            
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Login successful.'
        ];

        header("Location: dashboard.php");
        exit;
    }
}

/* ❌ Failed login */
$_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Invalid Student ID or Password.'  
];
//log failed attempts
$attempt = $conn->prepare("
    INSERT INTO login_attempts (user_type, user_id, ip_address, success)
    VALUES (?, ?, ?, 0)
");
$type = 'student';
$attempt->bind_param("sis", $type, $row['id'], $ip);
$attempt->execute();
//flag for too many failed attempts
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM login_attempts 
    WHERE ip_address=? 
    AND success=0
    AND created_at > NOW() - INTERVAL 10 MINUTE
");
$stmt->bind_param("s", $ip);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['total'];

if ($count >= 2) {

//prevent duplicate flags within 10 mins
    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='student'
        AND user_id=?
        AND reason='Multiple failed login attempts from same IP'
        AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $row['id']);
    $existing->execute();
    $existing_result = $existing->get_result();

    if ($existing_result->num_rows == 0) {
        // insert spam flag
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('student', ?, 'Multiple failed login attempts from same IP', 'medium')
        ");
    $flag->bind_param("i", $row['id']);
    $flag->execute();

       // risk score
    $user_type = 'student';
    $user_id   = $row['id'];

    addRisk($conn, $user_type, $user_id, 5);
}
}




header("Location: login_form.php");
exit;