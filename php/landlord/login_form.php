<?php
session_start();
require_once "../db_connect.php"; // adjust if your db file has a different name

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter both email and password";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM landlords WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $landlord = $result->fetch_assoc();

        // If using password_hash()
        if (password_verify($password, $landlord['password'])) {

            $_SESSION['user_id']   = $landlord['id'];
            $_SESSION['user_name'] = $landlord['full_name'];

            $_SESSION['success'] = "Welcome back, " . $landlord['user_name'] . " 👋";
            header("Location: landlord_dashboard.php");
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
    <title>Landlord Login</title>
    <link rel="stylesheet" href="../auth_styles.css">
    
</head>
<body>

<div class="auth-page">
      <div class="auth-container"> 

        <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">WELCOME TO H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>  

    <h2>Landlord Login</h2>

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

        <label>Email</label>
        <input type="email" name="email" required>

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