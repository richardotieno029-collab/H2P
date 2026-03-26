<?php
session_start();
require "../includes/config/db_connect.php";
require "../includes/mailer.php";

$email = $_POST['email'];

$ip = $_SERVER['REMOTE_ADDR'];

/* Same rate limit check */

$stmt = $conn->prepare("
SELECT COUNT(*) as total
FROM password_resets
WHERE email=? 
AND created_at > NOW() - INTERVAL 1 HOUR
");

$stmt->bind_param("s",$email);
$stmt->execute();
$emailCount=$stmt->get_result()->fetch_assoc()['total'];

if($emailCount>=3){

$_SESSION['toast']=[
"type"=>"error",
"message"=>"Too many reset requests. Try later."
];

header("Location: forgot_password.php?sent=1");
exit;
}

/* create token */

$token = bin2hex(random_bytes(32));

$stmt=$conn->prepare("
INSERT INTO password_resets
(email,ip_address,token,expires_at)
VALUES (?, ?, ?, DATE_ADD(NOW(),INTERVAL 15 MINUTE))
");

$stmt->bind_param("sss",$email,$ip,$token);
$stmt->execute();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];

$resetLink = $protocol.$domain."/H2P/php/auth/reset_password.php?token=".$token;

$subject="Password Reset";

$body="
Reset your password:

<a href='$resetLink'>Reset Password</a>

Link expires in 15 minutes.
";

sendMail($email,"",$subject,$body);

$_SESSION['toast']=[
"type"=>"success",
"message"=>"Reset email sent again."
];

header("Location: forgot_password.php?sent=1");
exit;