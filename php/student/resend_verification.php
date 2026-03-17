<?php
session_start();
include '../toast.php';
require_once "../db_connect.php";
require_once "../includes/mailer.php";

$email = $_POST['email'];

/* Find student */

$stmt = $conn->prepare("
SELECT id, full_name, verification_sent_at, email_verified
FROM students
WHERE email=?
");

$stmt->bind_param("s",$email);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    $_SESSION['toast'] = [
        'type'=>'info',
        'message'=>'Account not found.'
    ];
    header("Location: login_form.php");
    exit;
}

$user = $result->fetch_assoc();

/* Already verified */

if($user['email_verified'] == 1){
        $_SESSION['toast'] = [
            'type'=>'info',
            'message'=>'Email already verified. Please login'
        ];
        header("Location: login_form.php");
        exit;
}

/* Rate limit check */

if($user['verification_sent_at'] != null){

$last_sent = strtotime($user['verification_sent_at']);

if(time() - $last_sent < 3600){
        $_SESSION['toast'] = [
            'type'=>'info',
            'message'=>'Verification link already sent recently. Recheck your email.'
        ];
        header("Location: login_form.php?unverified=1&email=" . urlencode($email));
        exit;
}

}

/* Generate new token */

$token = bin2hex(random_bytes(32));

$stmt = $conn->prepare("
UPDATE students
SET verification_token=?,
token_expires=DATE_ADD(NOW(),INTERVAL 1 HOUR),
verification_sent_at=NOW()
WHERE id=?
");

$stmt->bind_param("si",$token,$user['id']);
$stmt->execute();

/* Build verification link */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];

$verify_link = $protocol.$domain."/H2P/student/verify_email.php?token=".$token;

/* Email */

$subject = "Verify your H2P account";

$body = "
Hello ".$user['full_name']."<br><br>

Click below to verify your account:<br><br>

<a href='$verify_link'>Verify Account</a><br><br>

This link expires in 1 hour.

";

sendMail($email,$user['full_name'],$subject,$body);

    $_SESSION['toast'] = [
        'type'=>'success',
        'message'=>'Verification email resent.'
    ];
    header("Location: login_form.php?unverified=1&email=" . urlencode($email));
    exit;
?>