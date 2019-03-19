<?php

use App\MailChecker;
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
    $scienerApi = new ScienerApi();

    foreach ($mailChecker->getMail() as $uid => $mail) {
        processMail($mail, $scienerApi);
        $mailChecker->setSeen($uid);
    }
}

function processMail(string $mail, ScienerApi $scienerApi): void {
    $parser = new Parser($mail);
    $checkInDate = $parser->getCheckInDate();
    $checkOutDate = $parser->getCheckOutDate();
    $guestName = $parser->getGuestName();
    $phone = $parser->getPhone();
    $password = (string) substr(str_replace(' ', '', $phone), -5);
    $result = $scienerApi->addPasscode($guestName, $password, prepareDate($checkInDate), prepareDate($checkOutDate));
    if (isset($result['keyboardPwdId'])) {
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
