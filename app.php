<?php

use App\BookingRepository;
use App\MailChecker;
use App\MailSender;
use App\ScienerApi;
use App\Logger;

require __DIR__ . '/vendor/autoload.php';

// Load .env
Dotenv\Dotenv::createImmutable(__DIR__)->load();

if (!isset($argv)) {
    Logger::log('Error: $argv has not been specified');
    exit;
}

$mailChecker = new MailChecker();
$mailSender = new MailSender();
$scienerApi = new ScienerApi();
$bookingRepository = new BookingRepository();
$app = new \App\App($mailChecker, $mailSender, $scienerApi, $bookingRepository);

try {
    switch ($argv[1]) {
        case 'reservationChecker':
            $app->runReservationChecker();
            break;
        case 'expiredPasscodesRemover':
            $app->runExpiredPasscodesRemover();
            break;
        case 'registerPasscodes':
            $app->runExpiredPasscodesRemover();
            break;
        default:
            Logger::log('run parameter not specified');
            exit;
    }
} catch (\Exception $e) {
    Logger::log("Error: {$e->getMessage()}");
}
