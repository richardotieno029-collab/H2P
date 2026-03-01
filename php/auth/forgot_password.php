<?php
session_start();
include "../toast.php";
require '../db_connect.php'; // your DB connection
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    // Check if email exists in students or landlords
    $stmt = $conn->prepare("SELECT email FROM students WHERE email=? 
                            UNION 
                            SELECT email FROM landlords WHERE email=?");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

    //token limit
    $ip = $_SERVER['REMOTE_ADDR'];

// Limit per email (3 per hour)
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM password_resets 
    WHERE email=? 
    AND created_at > NOW() - INTERVAL 1 HOUR
");
$stmt->bind_param("s", $email);
$stmt->execute();
$emailCount = $stmt->get_result()->fetch_assoc()['total'];

if ($emailCount >= 3) {
    die("Too many reset requests for this email. Try again later.");
}

// Limit per IP (5 per hour)
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM password_resets 
    WHERE ip_address=? 
    AND created_at > NOW() - INTERVAL 1 HOUR
");
$stmt->bind_param("s", $ip);
$stmt->execute();
$ipCount = $stmt->get_result()->fetch_assoc()['total'];

if ($ipCount >= 5) {
    die("Too many reset attempts from your network. Try again later.");
}
        // Delete old tokens
       $stmt = $conn->prepare("DELETE FROM password_resets WHERE email=? AND used=0");
$stmt->bind_param("s", $email);
$stmt->execute();

        $token = bin2hex(random_bytes(32));
        $expiry = null;

   $stmt = $conn->prepare("
    INSERT INTO password_resets (email, ip_address, token, expires_at)
    VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
");
$stmt->bind_param("sss", $email, $ip, $token);
$stmt->execute();

        $resetLink = "http://localhost/H2P/php/auth/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'richardotieno029@gmail.com';
            $mail->Password   = getenv('SMTP_PASS'); // Use environment variable for security
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('richardotieno029@gmail.com', 'H2P');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            $mail->Body    = "
                <h3>Password Reset Request</h3>
                <p>Click link below to reset password:</p>
                <a href='$resetLink'>$resetLink</a>
                <p>This link expires in 15 minutes.</p>
            ";

            $mail->send();
            $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Password reset link sent to your email.'
        ];
        header("Location: forgot_password.php");
exit;
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $_SESSION['toast'] = [
            'type' => 'info',
            'message' => 'Password reset link sent if email exists.'
        ];
        header("Location: forgot_password.php");
exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../auth_styles.css">
</head>
<body class="auth-page">
    <?php include "../toast.php"; ?>
    <div class="auth-container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <input type="email" name="email" required placeholder="Enter your email">
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>