<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Signup</title>
     <link rel="stylesheet" href="../auth_styles.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">
<div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
    <h2>Student Signup</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST" action="signup.php">
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone">
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="login_form.php">Login</a></p>
    </div>
</div>

</body>
</html>