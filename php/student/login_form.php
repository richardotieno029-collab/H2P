<?php
session_start();
require_once "../db_connect.php"; // adjust if your db file has a different name


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id    = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    if (empty($student_id) || empty($password)) {
        $_SESSION['error'] = "Please enter both student ID and password";
        header("Location: login_form.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student     = $result->fetch_assoc();

        // If using password_hash()
        if (password_verify($password, $student['password'])) {

            $_SESSION['student_id']   = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];

            $_SESSION['success'] = "Welcome back, " . $student['full_name'] . " 👋";
            header("Location: dashboard.php");
            exit;

        } else {
            $_SESSION['error'] = "Wrong email or password";
        }
    } else {
        $_SESSION['error'] = "Wrong email or password";
    }

    header("Location: login_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <link rel="stylesheet" href="../auth_styles.css">
    
</head>
<body>
<a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>

<div class="auth-page">
  <div class="auth-container"> 
    <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>     
    <h2>Student Login</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <?= $_SESSION['error']; ?>
        </div>
    <?php unset($_SESSION['error']); endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?= $_SESSION['success']; ?>
        </div>
    <?php unset($_SESSION['success']); endif; ?>

    <form method="POST" action="login.php">

        <label>Student ID</label>
        <input type="text" name="student_id" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>
    <div class="auth-footer">
            Don't have an account?
            <a href="signup_form.php">signup</a>
        </div>
        </div>
</div>

</body>
</html>