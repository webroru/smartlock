<?php

namespace App;

class Logger
{
    public static function log(string $message): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/../logs/app.log', "$date $message\n", FILE_APPEND);
    }

    public static function error(string $message, bool $sendMail = true): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        file_put_contents(__DIR__ . '/../logs/error.log', "$date $message\n", FILE_APPEND);
        if (!$sendMail) {
            return;
        }
        $mailSender = new MailSender();
        $mail = getenv('SUPPORT_EMAIL');
        $name = 'Administrator';
        $body = "An error occurred during the booking process!\n" .
            "$date $message\n";
        $mailSender->send($mail, $name, 'Error booking system', $body);
    }
}
