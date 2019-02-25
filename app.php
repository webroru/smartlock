<?php

use App\Parser;
use App\ScienerApi;

require __DIR__ . '/vendor/autoload.php';

$mail = file_get_contents('./mail.html');

$parser = new Parser($mail);
$scienerApi = new ScienerApi();

$checkInDate = $parser->getCheckInDate();
$checkOutDate = $parser->getCheckOutDate();
$bookingNumber = $parser->getBookingNumber();
$guestName = $parser->getGuestName();
$password = substr($bookingNumber, -6);
$checkInDateInMs = (new \DateTime($checkInDate))->getTimestamp() * 1000;
$checkOutDateInMs = (new \DateTime($checkOutDate))->getTimestamp() * 1000;

var_dump($scienerApi->addPasscode($guestName, $password, $checkInDateInMs, $checkOutDateInMs));
