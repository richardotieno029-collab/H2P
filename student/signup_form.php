<?php
session_start();
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Signup</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>

<?php include "../includes/toast.php"; ?>

<div class="auth-page">
      <div class="auth-container"> 
        <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
        <div class="logo-container">
    <img src="../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">WELCOME TO H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
<h2>Student Signup</h2>

<form method="POST" action="signup.php" enctype="multipart/form-data" onsubmit="return handleSubmit(this, 'Creating your account...')">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <label>Full Name</label>
    <input type="text" name="full_name" placeholder="Firstname Othername" required>

    <label>Student Email</label>
    <input type="email" name="email" placeholder="12345@student.embuni.ac.ke" pattern="^[0-9]{5}@student\.embuni\.ac\.ke$" title="Use your student email (e.g. 12345@student.embuni.ac.ke)" required>

    <label>Phone</label>
    <input type="tel" name="phone" placeholder="0712345678" pattern="(07|01)[0-9]{8}|(2547|2541)[0-9]{8}" title="Valid formats: 0712345678, 0112345678, 254712345678, 254112345678" required>

    <label>Password</label>
    <input type="password" name="password" pattern="(?=.*[0-9])(?=.*[A-Za-z]).{8,}" title="At least 8 characters including letters and numbers" required>

    <label>Profile Picture (optional)</label>
    <input type="file" name="profile_pic" accept="image/*" data-max-size="5242880">

    <label class="checkbox-label">
        <input type="checkbox" name="agree_terms" required>
        I agree to the <a href="../terms.php" target="_blank">Terms & Conditions</a> and <a href="../privacy.php" target="_blank">Privacy Policy</a>.
    </label>

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