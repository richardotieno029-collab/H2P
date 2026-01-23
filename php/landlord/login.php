<?php
session_start();
require_once "../db_connect.php";

/* 1. Ensure form was submitted */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login_form.php");
    exit;
}

/* 2. Get & sanitize inputs */
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

/* 3. Basic validation */
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Email and password are required";
    header("Location: login_form.php");
    exit;
}

/* 4. Fetch landlord by email */
$sql = "SELECT landlord_id, email, password FROM landlords WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

/* 5. Check user exists */
if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Invalid email or password";
    header("Location: login_form.php");
    exit;
}

$row = $result->fetch_assoc();

/* 6. Verify password */
if (!password_verify($password, $row['password'])) {
    $_SESSION['error'] = "Invalid email or password";
    header("Location: login_form.php");
    exit;
}

/* 7. Login successful → create session */
$_SESSION['user_id'] = $row['landlord_id'];
$_SESSION['landlord_email'] = $row['email'];
$_SESSION['user_name'] = $row['name'];
$_SESSION['user_role'] = 'landlord';
$_SESSION['success'] = "Login successful";

/* 8. Redirect to dashboard */
header("Location: landlord_dashboard.php");
exit;