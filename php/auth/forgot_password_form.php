<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body class="auth-page">

<?php include "../toast.php"; ?>

<div class="auth-container">

<h2>Forgot Password</h2>

<?php if(isset($_GET['sent'])): ?>

<p>Check your email for a password reset link.</p>

<form action="resend_reset.php" method="POST">

<input type="hidden" name="email" value="<?php echo $_SESSION['reset_email']; ?>">

<button type="submit" id="resendBtn" disabled>
Resend Reset Email (<span id="countdown">60</span>s)
</button>

</form>

<?php else: ?>

<form action="forgot_password.php" method="POST">

<input type="email" name="email" required placeholder="Enter your email">

<button type="submit">Send Reset Link</button>

</form>

<?php endif; ?>

</div>
<script>

const button = document.getElementById("resendBtn");
const countdown = document.getElementById("countdown");

const cooldown = 60; // seconds

// Check if timer already exists
let resendTime = localStorage.getItem("resendCooldown");

if (!resendTime) {
    resendTime = Date.now() + cooldown * 1000;
    localStorage.setItem("resendCooldown", resendTime);
}

function updateTimer(){

    let remaining = Math.floor((resendTime - Date.now()) / 1000);

    if(remaining <= 0){

        button.disabled = false;
        button.textContent = "Resend Reset Email In";

        localStorage.removeItem("resendCooldown");

        return;

    }

    countdown.textContent = remaining;

}

updateTimer();

const timer = setInterval(function(){

    updateTimer();

    if(!localStorage.getItem("resendCooldown")){
        clearInterval(timer);
    }

},1000);

</script>

</body>
</html>