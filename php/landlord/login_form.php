<?php
session_start();
include "../toast.php";
$_SESSION['token'] = bin2hex(random_bytes(32)); // CSRF token generation
require_once "../db_connect.php"; // adjust if your db file has a different name

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
    <title>Landlord Login</title>
    <link rel="stylesheet" href="../auth_styles.css">
    
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

    <h2>Landlord Login</h2>

    <form method="POST" action="login.php">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>
    <div class="auth-footer">
         <a href="../auth/forgot_password.php">Forgot Password</a></br>
            Don't have an account?
            <a href="signup_form.php">signup</a>
        </div>
        </div>
</div>

</body>
</html>