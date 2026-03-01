<?php
session_start();
require_once "../db_connect.php";
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

$stmt = $conn->prepare(
    "SELECT id, student_id, full_name, password, profile_image 
     FROM students WHERE student_id = ?"
);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {

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
header("Location: login_form.php");
exit;