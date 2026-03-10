<?php
session_start();
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Signup</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<?php include "../toast.php"; ?>

<div class="auth-page">
    <div class="auth-container">
        <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">WELCOME TO H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>  

        <h2>Landlord Signup</h2>

        <!-- IMPORTANT: multipart/form-data -->
        <form method="POST" action="signup.php" enctype="multipart/form-data">

        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
            <label>Full Name</label>
            <input type="text" name="name" required>

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
            <a href="login.php">Login</a>
        </div>

    </div>
</div>

</body>
</html>