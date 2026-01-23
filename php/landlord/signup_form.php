<?php
session_start();
require_once "../db_connect.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $message = "All fields are required.";
        $message_type = "alert-error";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT landlord_id FROM landlords WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email already registered. Please try again.";
            $message_type = "alert-error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO landlords (full_name, email, phone, password) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Account created successfully. Please login.";
                header("Location: login_form.php");
                exit;
            } else {
                $message = "Signup failed. Please try again.";
                $message_type = "alert-error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Signup</title>
    <link rel="stylesheet" href="../auth_styles.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">
<div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">WELCOME TO H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>  

        <h2>Landlord Signup</h2>

        <?php if ($message): ?>
            <div class="alert <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Full Name</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Phone</label>
            <input type="text" name="phone" required>

            <label>Password</label>
            <input type="password" name="password" required>

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