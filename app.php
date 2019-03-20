<?php

use App\MailChecker;
use App\MailSender;
use App\Parser;
use App\ScienerApi;

require __DIR__ . '/vendor/autoload.php';

try {
    run();
} catch (\Exception $e) {
    addLog("Error: {$e->getMessage()}");
}

function run() {
    $mailChecker = new MailChecker();
    $mailSender = new MailSender();
    $scienerApi = new ScienerApi();

    foreach ($mailChecker->getMail() as $uid => $mail) {
        processMail($mail, $scienerApi, $mailSender);
        $mailChecker->setSeen($uid);
    }
}

function processMail(string $mail, ScienerApi $scienerApi, MailSender $mailSender): void {
    $parser = new Parser($mail);
    $checkInDate = $parser->getCheckInDate();
    $checkOutDate = $parser->getCheckOutDate();
    $guestName = $parser->getGuestName();
    $phone = $parser->getPhone();
    $email = $parser->getEmail();
    $password = (string) substr(str_replace(' ', '', $phone), -5);
    $result = $scienerApi->addPasscode($guestName, $password, prepareDate($checkInDate), prepareDate($checkOutDate));
    if (isset($result['keyboardPwdId'])) {
        if ($email) {
            $email = 'sersitki@gmail.com';
            sendMail($mailSender, $email, $password);
        }
        addLog("For $guestName have been added password: $password valid from $checkInDate to $checkOutDate");
    } elseif (isset($result['errmsg'])) {
        addLog("Error during processing for $guestName: {$result['errmsg']}");
    } else {
        addLog("Unknown error during processing for $guestName");
    }
}

function prepareDate(string $date): int {
    return (new \DateTime("$date 12:00", new \DateTimeZone('Europe/Vienna')))
        ->getTimestamp() * 1000;
}

function addLog(string $message): void {
    $date = (new \DateTime())->format('Y-m-d H:i:s');
    echo "$date $message\n";
}

function sendMail(MailSender $mailSender, string $mail, string $password): void {
    $body = "Hotel «GreenSLO» Ljubljana.\n" .
        "Your CODE from the MAIN DOOR of the HOTEL:  # $password #\n" .
        "This CODE will be VALID from the time of сheck-in and until check-out\n" .
        "(14:00 - check in, 12:00 - check out)\n" .
        "I ask you to SAVE this CODE to enter the hotel.\n" .
        "Best regards, Sergey";

    $mailSender->send($mail,'Hotel GreenSLO Ljubljana', $body);
}