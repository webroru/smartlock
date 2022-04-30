<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

class MailSender
{
    private const HOST = 'smtp.gmail.com';
    private const PORT = 587;
    private const SECURE = 'tls';

    protected $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = self::HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = getenv('MAIL_USER');
        $this->mail->Password = getenv('MAIL_PASSWORD');
        $this->mail->SMTPSecure = self::SECURE;
        $this->mail->Port = self::PORT;
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        $this->mail->setFrom(getenv('MAIL_USER'), getenv('MAIL_NAME'));
    }

    public function send(string $mail, string $name, string $subject, string $body): void
    {
        $this->mail->Subject = $subject;
        $this->mail->Body = $body;
        $this->mail->addAddress($mail, $name);
        if (!$this->mail->send()) {
            $this->mail->clearAddresses();
            throw new \Exception("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
        $this->mail->clearAddresses();
    }
}
