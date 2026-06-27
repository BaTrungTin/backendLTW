<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;

class MailHelper
{
    public static function sendMail(string $to, string $subject, string $html): bool
    {
        $user = $GLOBALS['config']['gmail_user'] ?? '';
        $pass = $GLOBALS['config']['gmail_pass'] ?? '';
        if ($user === '' || $pass === '') {
            error_log("Mail skipped (no GMAIL config): $to - $subject");
            return false;
        }
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom($user, 'Tour');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->CharSet = 'UTF-8';
        return $mail->send();
    }
}
