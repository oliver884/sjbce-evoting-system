<?php
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Send an email via Gmail SMTP.
 * Returns true on success, false on failure (check error_log for details).
 */
function send_email(string $toEmail, string $toName, string $subject, string $htmlBody, string $plainBody = ''): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('Email send failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send the password reset email with a clickable link.
 */
function send_password_reset_email(string $toEmail, string $toName, string $resetLink): bool
{
    $subject = 'Reset your SJBCE Election Portal password';
    $html = "
        <p>Hello " . htmlspecialchars($toName) . ",</p>
        <p>We received a request to reset your password for the SJBCE Election Portal.</p>
        <p><a href=\"" . htmlspecialchars($resetLink) . "\" style=\"background:#E31E2C;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block;\">Reset Password</a></p>
        <p>Or copy and paste this link into your browser:<br>" . htmlspecialchars($resetLink) . "</p>
        <p>This link expires in 1 hour. If you didn't request this, you can safely ignore this email.</p>
    ";
    return send_email($toEmail, $toName, $subject, $html);
}
