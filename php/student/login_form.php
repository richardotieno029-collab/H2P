<?php
session_start();
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation
include "../toast.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<?php include "../toast.php"; ?>
<div class="auth-page">
      <div class="auth-container"> 
        
<a href="../index/index.php" class="back-btn" title="Go back">
    ←
</a>
        <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">WELCOME TO H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>

<h2>Student Login</h2>
<form method="POST" action="login.php">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <label>Student Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>
<div class="auth-footer">
    <a href="../auth/forgot_password_form.php">Forgot Password</a></br>
            Don't have an account?
<a href="signup_form.php">Signup</a><br>
        </div>
        </div>
        </div>

</body>
</html>