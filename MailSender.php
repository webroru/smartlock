<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailSender
{
    const HOST = 'smtp.gmail.com';
    const MAIL = 'booker.greenslo@gmail.com';
    const PASSWORD = 'Reenslog19';
    const PORT = 587;
    const SECURE = 'tls';
    const USERNAME = 'Family Guesthouse GreenSLO';

    protected $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = self::HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = self::MAIL;
        $this->mail->Password = self::PASSWORD;
        $this->mail->SMTPSecure = self::SECURE;
        $this->mail->Port = self::PORT;
        $this->mail->setFrom(self::MAIL, self::USERNAME);
    }

    public function send(string $mail, string $subject, string $body)
    {
        try {
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->addAddress($mail);
            $this->mail->send();
        } catch (Exception $e) {
            \addLog("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }
}