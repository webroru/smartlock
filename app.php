<?php

use App\Logger;
use App\MailChecker;
use App\MailSender;
use App\Repository\BookingMysqlRepository;
use App\ScienerApi;

require __DIR__ . '/vendor/autoload.php';

// Load .env
Dotenv\Dotenv::createImmutable(__DIR__)->load();

if (!isset($argv)) {
    Logger::error('$argv has not been specified');
    exit;
}

$mailChecker = new MailChecker();
$mailSender = new MailSender();
$scienerApi = new ScienerApi();
$bookingRepository = new BookingMysqlRepository();
$app = new \App\App($mailChecker, $mailSender, $scienerApi, $bookingRepository);

try {
    switch ($argv[1]) {
        case 'reservationChecker':
            $app->runReservationChecker();
            break;
        case 'expiredPasscodesRemover':
            $app->runExpiredPasscodesRemover();
            break;
        case 'checkDelayedBooking':
            $app->checkDelayedBooking();
            break;
        default:
            throw new Exception('run parameter not specified');
    }
} catch (\Exception $e) {
    Logger::error($e->getMessage());
}
