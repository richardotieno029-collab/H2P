<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

function sendMail($toEmail, $toName, $subject, $body){

    $mail = new PHPMailer(true);

    try{

        /* SMTP CONFIGURATION */

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';   // change later if hosting email used
        $mail->SMTPAuth = true;

        $mail->Username = 'no-reply@h2p.co.ke'; 
        $mail->Password = 'EMAIL_PASSWORD';    

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        /* EMAIL DETAILS */

        $mail->setFrom('no-reply@h2p.co.ke', 'H2P System');

        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();

        return true;

    }catch(Exception $e){

        return false;

    }
}