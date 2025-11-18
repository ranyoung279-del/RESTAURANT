<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer; // an toàn: PHP không lỗi nếu class chưa autoload
use PHPMailer\PHPMailer\Exception;

final class Email {
    public static function send(string $to, string $subject, string $body): void {
        $sent = false;
        $host     = getenv('SMTP_HOST') ?: '';
        $user     = getenv('SMTP_USER') ?: '';
        $pass     = getenv('SMTP_PASS') ?: '';
        $port     = (int)(getenv('SMTP_PORT') ?: 0);
        $secure   = getenv('SMTP_SECURE') ?: 'tls';
        $from     = getenv('SMTP_FROM') ?: ($user ?: 'no-reply@example.com');
        $fromName = getenv('SMTP_FROM_NAME') ?: 'System';

        $canSmtp = $host && $user && $pass && $port && class_exists('PHPMailer\\PHPMailer\\PHPMailer');

        if ($canSmtp) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $host;
                $mail->SMTPAuth = true;
                $mail->Username = $user;
                $mail->Password = $pass;
                if ($secure === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } elseif ($secure === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }
                if ($port > 0) $mail->Port = $port;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom($from, $fromName);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $body;
                $mail->send();
                $sent = true;
            } catch (\Throwable $e) {
                // fall back below
            }
        }

        if (!$sent) {
            $headers = "Content-Type: text/plain; charset=UTF-8\r\nFrom: $from";
            @mail($to, $subject, $body, $headers);
        }

        $logLine = date('c') . " | TO: $to | SUBJECT: $subject | SENT=" . ($sent ? 'SMTP' : 'mail()') . "\n$body\n-----------------------------\n";
        $mailboxFile = __DIR__ . '/../../simulated_mailbox.txt';
        file_put_contents($mailboxFile, $logLine, FILE_APPEND);
    }
}
