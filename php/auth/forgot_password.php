<?php
session_start();
require "../includes/config/db_connect.php";
require "../includes/mailer.php";

if($_SERVER["REQUEST_METHOD"] !== "POST"){
    header("Location: forgot_password.php");
    exit;
}

$email = trim($_POST['email']);
$ip = $_SERVER['REMOTE_ADDR'];

/* Check if email exists */

$stmt = $conn->prepare("
SELECT email FROM students WHERE email=? 
UNION 
SELECT email FROM landlords WHERE email=?
");

$stmt->bind_param("ss",$email,$email);
$stmt->execute();
$result = $stmt->get_result();

/* Rate limit */

$stmt = $conn->prepare("
SELECT COUNT(*) as total
FROM password_resets
WHERE email=? 
AND created_at > NOW() - INTERVAL 1 HOUR
");

$stmt->bind_param("s",$email);
$stmt->execute();
$emailCount = $stmt->get_result()->fetch_assoc()['total'];

if($emailCount >= 3){

$_SESSION['toast']=[
"type"=>"error",
"message"=>"Too many reset requests. Try again later."
];

header("Location: forgot_password.php");
exit;
}

/* If account exists send reset */

if($result->num_rows > 0){

$stmt = $conn->prepare("
DELETE FROM password_resets
WHERE email=? AND used=0
");
$stmt->bind_param("s",$email);
$stmt->execute();

$token = bin2hex(random_bytes(32));

$stmt = $conn->prepare("
INSERT INTO password_resets
(email, ip_address, token, expires_at)
VALUES (?, ?, ?, DATE_ADD(NOW(),INTERVAL 15 MINUTE))
");

$stmt->bind_param("sss",$email,$ip,$token);
$stmt->execute();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];

$resetLink = $protocol.$domain."/H2P/php/auth/reset_password.php?token=".$token;

$subject="Password Reset";

$body="
<h3>Password Reset Request</h3>

Click below to reset your password:

<br><br>

<a href='$resetLink'>Reset Password</a>

<br><br>

This link expires in 15 minutes.
";

sendMail($email,"",$subject,$body);

}

$_SESSION['toast']=[
"type"=>"success",
"message"=>"If the email exists, a reset link has been sent."
];

$_SESSION['reset_email']=$email;

header("Location: forgot_password_form.php?sent=1");
exit;