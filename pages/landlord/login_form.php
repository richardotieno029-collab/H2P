<?php
session_start();
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation
require_once "../includes/config/db_connect.php";


$unverified = isset($_GET['unverified']) && $_GET['unverified'] == '1';
$unverifiedEmail = htmlspecialchars($_GET['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
         $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Please enter both email and password.'
];
        header("Location: login_form.php");
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

            // If email not verified, redirect back to login and show resend link
            if ($landlord['email_verified'] == 0) {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'message' => 'Please verify your email before logging in.'
                ];
                $redirect = "login_form.php?unverified=1&email=" . urlencode($landlord['email']);
                header("Location: $redirect");
                exit;
            }

            $_SESSION['user_id']   = $landlord['id'];
            $_SESSION['user_name'] = $landlord['full_name'];

            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Login successful. You are logged in as ' . htmlspecialchars($landlord['full_name']) . '.'
            ];
            $_SESSION['token'] = bin2hex(random_bytes(32));
            header("Location: landlord_dashboard.php");
            exit;

        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Wrong email or password.'
            ];
        }
    } else {
        $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Wrong email or password.'
];
    }

    header("Location: login_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Login</title>
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

    <h2>Landlord Login</h2>

    <form method="POST" action="login.php" onsubmit="return handleSubmit(this, 'Logging in...')">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

        <label>Email or Phone</label>
    <input type="text" name="email" required value="<?= $unverifiedEmail ?>" placeholder="e.g. 0712345678 or you@domain.com">

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>

    </form>

    <?php if ($unverified && $unverifiedEmail): ?>
        <div class="resend-section">
            <p>
                Email <strong><?= $unverifiedEmail ?></strong> is not verified yet.
                You can resend the verification link below.
            </p>
            <form method="POST" action="resend_verification.php" onsubmit="return handleSubmit(this, 'Resending verification email...')">
                <input type="hidden" name="email" value="<?= $unverifiedEmail ?>">
                <button type="submit" id="resendBtn" disabled>
                    Resend Verification Email (<span id="countdown">60</span>s)
                </button>
            </form>
        </div>

        <script>
            const resendBtn = document.getElementById('resendBtn');
            const countdown = document.getElementById('countdown');
            const key = 'resendCooldown_landlord_<?= rawurlencode($unverifiedEmail) ?>';

            let resendTime = localStorage.getItem(key);
            if (!resendTime) {
                resendTime = Date.now() + 60 * 1000;
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

         <a href="../auth/forgot_password_form.php">Forgot Password</a></br>
            Don't have an account?
            <a href="signup_form.php">signup</a>
        </div>
        </div>
</div>

</body>
</html>