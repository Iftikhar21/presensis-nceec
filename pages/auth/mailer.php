<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = 'smtp.gmail.com'; // Ganti dengan host SMTP Anda
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587; // Ganti dengan port SMTP Anda
// $mail->SMTPDebug = 2;
$mail->Username = 'neorozatech@gmail.com'; // Ganti dengan username SMTP Anda
$mail->Password = 'vgml ebrd qpvr bljz'; // Ganti dengan password SMTP Anda

$mail->isHTML(true);

return $mail;