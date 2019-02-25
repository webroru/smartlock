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
    $bookingNumber = $parser->getBookingNumber();
    $guestName = $parser->getGuestName();
    $password = substr($bookingNumber, -6);
    $checkInDateInMs = (new \DateTime($checkInDate))->setTime(12, 0)->getTimestamp() * 1000;
    $checkOutDateInMs = (new \DateTime($checkOutDate))->setTime(12, 0)->getTimestamp() * 1000;
    if ($scienerApi->addPasscode($guestName, $password, $checkInDateInMs, $checkOutDateInMs)) {
        echo "For $guestName have been added password: $password valid from $checkInDate to $checkOutDate";
    }
}
