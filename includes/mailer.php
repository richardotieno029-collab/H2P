<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

function sendMail($toEmail, $toName, $subject, $body){

    $mail = new PHPMailer(true);

   try{

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

$mail->Username = 'richardotieno029@gmail.com';
$mail->Password = 'no password to show';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('richardotieno029@gmail.com', 'H2P Test');

$mail->addAddress($toEmail, $toName);

$mail->isHTML(true);

$mail->Subject = $subject;
$mail->Body = $body;

// Disable verbose output (prevents mail debug being shown on pages)
$mail->SMTPDebug = 0;
$mail->Debugoutput = 'error_log';

$mail->send();

return true;

} catch(Exception $e) {
    // Log the real error so it can be diagnosed
    error_log('PHPMailer error: ' . $e->getMessage());
    // Return the message so callers can display a friendly toast when desired.
    return $e->getMessage();
}
}

/**
 * Send mail without affecting the application's control flow.
 * Returns true on success, false on failure.
 */
function sendMailQuiet($toEmail, $toName, $subject, $body) {
    $result = sendMail($toEmail, $toName, $subject, $body);
    return $result === true;
}
