<?php

use App\BookingRepository;
use App\MailChecker;
use App\MailSender;
use App\Parser;
use App\ScienerApi;

require __DIR__ . '/vendor/autoload.php';

const REGISTRATION_DELAY = 60 * 60 * 24 * 7; // 1 week

// Load .env
Dotenv\Dotenv::createImmutable(__DIR__)->load();

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
        case 'registerPasscodes':
            runExpiredPasscodesRemover();
            break;
        default:
            addLog('run parameter not specified');
            exit;
    }
} catch (\Exception $e) {
    addLog("Error: {$e->getMessage()}");
}

function runReservationChecker(): void
{
    $mailChecker = new MailChecker();
    $mailSender = new MailSender();
    $scienerApi = new ScienerApi();
    $bookingRepository = new BookingRepository();

    foreach ($mailChecker->getMail() as $uid => $mail) {
        processMail($mail, $scienerApi, $mailSender, $bookingRepository);
        $mailChecker->setSeen($uid);
    }
}

function runExpiredPasscodesRemover(): void
{
    $scienerApi = new ScienerApi();
    $scienerApi->removeExpiredPasscodes();
    addLog('Expired passcodes removed successfully');
}

function processMail(string $mail, ScienerApi $scienerApi, MailSender $mailSender, BookingRepository $bookingRepository): void
{
    $parser = new Parser($mail);
    $checkInDate = $parser->getCheckInDate();
    $checkOutDate = $parser->getCheckOutDate();
    $guestName = $parser->getGuestName();
    $email = $parser->getEmail();
    $isChanged = $parser->isChanged();
    $email = $email !== '' ? $email : getenv('SUPPORT_EMAIL');
    $booking = (new \App\Booking())
        ->setName($guestName)
        ->setEmail($email)
        ->setCheckInDate(new \DateTime($checkInDate)) // использовать prepareCheckInDate
        ->setCheckOutDate(new \DateTime($checkOutDate));

    if (time() - strtotime($checkInDate) <= REGISTRATION_DELAY) {
        registerBooking($booking, $scienerApi, $mailSender, $isChanged);
    } else {
        $bookingRepository->add($booking);
    }
}

function prepareCheckInDate(string $date): int
{
    return (new \DateTime("$date 14:00", new \DateTimeZone('Europe/Vienna')))
        ->getTimestamp() * 1000;
}

function prepareCheckOutDate(string $date): int
{
    return (new \DateTime("$date 12:00", new \DateTimeZone('Europe/Vienna')))
        ->getTimestamp() * 1000;
}

function addLog(string $message): void
{
    $date = (new \DateTime())->format('Y-m-d H:i:s');
    echo "$date $message\n";
}

function sendMail(
    MailSender $mailSender,
    \App\Booking $booking,
    string $password,
    bool $isChanged
): void {
    $body = "Dear {$booking->getName()}\n" .
        'You have ' . ($isChanged ? 'changes ' : 'a ') .
        "reservation at the Hotel \"GreenSLO\" from {$booking->getCheckInDate()->format('Y-m-d H:i')}" .
        "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}\n" .
        "Your CODE from the MAIN DOOR of the HOTEL:  #$password#\n" .
        "This CODE will be VALID from the time of check-in and until check-out\n" .
        "(14:00 - check in, 12:00 - check out)\n" .
        "I ask you to SAVE this CODE to enter the hotel.\n" .
        "Best regards, Sergey";

    $mailSender->send($booking->getEmail(), $booking->getName(), 'Hotel GreenSLO Ljubljana', $body);
}

function registerBooking(\App\Booking $booking, ScienerApi $scienerApi, MailSender $mailSender, bool $isChanged): void
{
    $password = $scienerApi->generatePasscode(
        $booking->getName(),
        $booking->getCheckInDate()->getTimestamp() * 1000,
        $booking->getCheckOutDate()->getTimestamp() * 1000
    );
    sendMail($mailSender, $booking, $password, $isChanged);
    addLog(
        "For {$booking->getName()} have been added password: $password valid from " .
        "{$booking->getCheckInDate()->format('Y-m-d H:i')} " .
        "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}"
    );
}
