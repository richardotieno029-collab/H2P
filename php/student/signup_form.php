<?php
session_start();
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation
include "../toast.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Signup</title>
    <link rel="stylesheet" href="../auth_styles.css">
</head>
<body>

<?php include "../toast.php"; ?>

<div class="auth-page">
      <div class="auth-container"> 
<a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
        <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">WELCOME TO H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
<h2>Student Signup</h2>

<form method="POST" action="signup.php" enctype="multipart/form-data">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <label>Student ID</label>
    <input type="text" name="student_id" required>

    <label>Full Name</label>
    <input type="text" name="full_name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Phone</label>
    <input type="text" name="phone" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Profile Picture (optional)</label>
    <input type="file" name="profile_pic" accept="image/*">

    <button type="submit">Create Account</button>
</form>
<div class="auth-footer">
            Already have an account?
            <a href="login_form.php">Login</a>
        </div>
        </div>
        </div>

</body>
</html>