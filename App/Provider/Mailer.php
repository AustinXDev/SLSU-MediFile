<?php

namespace App\Provider;

//require_once __DIR__ . '/../../config/init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // SMTP
        $this->mail->isSMTP();

        $this->mail->Host = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth = true;

        $this->mail->Username = $_ENV['SMTP_USERNAME'];
        $this->mail->Password = $_ENV['SMTP_PASSWORD'];

        $this->mail->SMTPSecure =
            $_ENV['SMTP_ENCRYPTION'] === 'tls'
                ? PHPMailer::ENCRYPTION_STARTTLS
                : PHPMailer::ENCRYPTION_SMTPS;

        $this->mail->Port = (int) $_ENV['SMTP_PORT'];

        // Sender
        $this->mail->setFrom(
            $_ENV['SMTP_FROM'],
            $_ENV['SMTP_FROM_NAME']
        );

        // HTML email
        $this->mail->isHTML(true);

        // Character encoding
        $this->mail->CharSet = 'UTF-8';
    }


    public function send(
        string $to,
        string $subject,
        string $body
    ): bool {

        try {

            $this->mail->clearAddresses();

            $this->mail->addAddress($to);

            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            return $this->mail->send();

        } catch (Exception $e) {

            error_log(
                'Email error: ' . $e->getMessage()
            );

            return false;
        }
    }
}