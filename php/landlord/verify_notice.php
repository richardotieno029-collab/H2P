<?php
session_start();
$email = $_GET['email'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <link rel="stylesheet" href="../styles.css">

</head>
<body>
<?php include "../toast.php"; ?>
<div class="auth-page">
      <div class="auth-container"> 
    <h2>Verify Your Email</h2>

    <p>
        We sent a verification email to: <strong><?php echo htmlspecialchars($email); ?></strong>.
    </p>

    <p>
        Please check your inbox and click the activation link then click <a href="login_form.php">Login</a>
         to access your account.
    </p>

    <form action="resend_verification.php" method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <button type="submit">Resend Verification Email</button>
    </form>
    </div>
    </div>
</body>
</html>