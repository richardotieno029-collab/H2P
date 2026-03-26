<?php
session_start();
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation

$unverified = isset($_GET['unverified']) && $_GET['unverified'] == '1';
$unverifiedEmail = htmlspecialchars($_GET['email'] ?? '');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>

<?php include "../includes/toast.php"; ?>
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
<form method="POST" action="login.php" onsubmit="return handleSubmit(this, 'Logging in...')">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <label>Email or Phone</label>
    <input type="text" name="email" required value="<?= $unverifiedEmail ?>" placeholder="e.g. 0712345678 or 12345@student.embuni.ac.ke">

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<?php if ($unverified && $unverifiedEmail): ?>
    <div class="resend-section">
        <p>We sent a verification email to <strong><?= $unverifiedEmail ?></strong>. Didn’t receive it?</p>
        <form action="../student/resend_verification.php" method="POST" onsubmit="return handleSubmit(this, 'Resending verification email...')">
            <input type="hidden" name="email" value="<?= $unverifiedEmail ?>">
            <button type="submit" id="resendBtn" disabled>
                Resend Verification Email (<span id="countdown">60</span>s)
            </button>
        </form>
    </div>

    <script>
        const resendBtn = document.getElementById('resendBtn');
        const countdown = document.getElementById('countdown');
        const key = 'resendCooldown_student_<?= rawurlencode($unverifiedEmail) ?>';

        let resendTime = localStorage.getItem(key);
        if (!resendTime) {
            resendTime = Date.now() + 30 * 1000;
            localStorage.setItem(key, resendTime);
        }

        function updateTimer() {
            const remaining = Math.floor((resendTime - Date.now()) / 1000);

            if (remaining <= 0) {
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Verification Email';
                localStorage.removeItem(key);
                return;
            }

            countdown.textContent = remaining;
        }

        updateTimer();

        const timer = setInterval(() => {
            updateTimer();
            if (!localStorage.getItem(key)) {
                clearInterval(timer);
            }
        }, 1000);
    </script>
<?php endif; ?>

<div class="auth-footer">
    <a href="../auth/forgot_password_form.php">Forgot Password</a></br>
            Don't have an account?
<a href="signup_form.php">Signup</a><br>
        </div>
        </div>
        </div>

</body>
</html>