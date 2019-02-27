<?php

use App\MailChecker;
use App\Parser;
use App\ScienerApi;

require __DIR__ . '/vendor/autoload.php';

$mailChecker = new MailChecker();
$scienerApi = new ScienerApi();

foreach ($mailChecker->getMail() as $mail) {
    $parser = new Parser($mail);
    $checkInDate = $parser->getCheckInDate();
    $checkOutDate = $parser->getCheckOutDate();
    $guestName = $parser->getGuestName();
    $phone = $parser->getPhone();
    $password = substr(str_replace(' ', '', $phone), -5);
    $checkInDateInMs = (new \DateTime($checkInDate))->setTime(12, 0)->getTimestamp() * 1000;
    $checkOutDateInMs = (new \DateTime($checkOutDate))->setTime(12, 0)->getTimestamp() * 1000;
    if ($scienerApi->addPasscode($guestName, $password, $checkInDateInMs, $checkOutDateInMs)) {
        $date = (new \DateTime())->format('Y-m-d h:i:s');
        echo "$date For $guestName have been added password: $password valid from $checkInDate to $checkOutDate\n";
    }
}
