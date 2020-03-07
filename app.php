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
    Logger::error('$argv has not been specified');
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
            throw new Exception('run parameter not specified');
    }
} catch (\Exception $e) {
    Logger::error($e->getMessage());
}
