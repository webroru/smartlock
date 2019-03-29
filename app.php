<?php

use App\MailChecker;
use App\MailSender;
use App\Parser;
use App\ScienerApi;

require __DIR__ . '/vendor/autoload.php';

if (!isset($argv)) {
    addLog('Error: $argv disabled');
    exit;
}

try {
    switch ($argv[1]) {
        case 'reservationChecker':
            runReservationChecker();
            break;
        case 'expiredPasscodesRemover':
            runExpiredPasscodesRemover();
            break;
        default:
            addLog('run parameter not specified');
            exit;
    }
} catch (\Exception $e) {
    addLog("Error: {$e->getMessage()}");
}

function runReservationChecker() {
    $mailChecker = new MailChecker();
    $mailSender = new MailSender();
    $scienerApi = new ScienerApi();

    foreach ($mailChecker->getMail() as $uid => $mail) {
        processMail($mail, $scienerApi, $mailSender);
        $mailChecker->setSeen($uid);
    }
}

function runExpiredPasscodesRemover() {
    $scienerApi = new ScienerApi();
    $scienerApi->removeExpiredPasscodes();
    addLog('Expired passcodes removed successfully');
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
            sendMail($mailSender, $guestName, $email, $password, $checkInDate, $checkOutDate);
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

function sendMail(MailSender $mailSender, string $guestName, string $mail, string $password, string $checkInDate, string $checkOutDate): void {
    $body = "Dear $guestName\n" .
        "You have a reservation at the Hotel \"GreenSLO\"  from $checkInDate to $checkOutDate\n" .
        "Your CODE from the MAIN DOOR of the HOTEL:  # $password #\n" .
        "This CODE will be VALID from the time of сheck-in and until check-out\n" .
        "(14:00 - check in, 12:00 - check out)\n" .
        "I ask you to SAVE this CODE to enter the hotel.\n" .
        "Best regards, Sergey";

    $mailSender->send($mail, $guestName,'Hotel GreenSLO Ljubljana', $body);
}