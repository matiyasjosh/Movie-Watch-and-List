<?php
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



function sendEmail($email, $name, $token)
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls"; // or "ssl"
    $mail->Port = 587; // or 465 for SSL
    $mail->Host = "smtp.gmail.com";
    $mail->Username = "torbaw457@gmail.com";
    $mail->Password = "xjiy gzbe cvcu gugl";
    
    $mail->setFrom("torbaw457@gmail.com", "Mati Wolde");
    $mail->addAddress($email, $name);
    
    $mail->Subject = "Email Verification";
    $mail->msgHTML('Click <a href="http://localhost/custom-api/verifiyToken.php?token=' . $token . '">here</a> to activate your account.');

    $mail->send();
}

?>