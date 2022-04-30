<?php

use App\Commands\RemoveExpiredPasscodes;
use App\Logger;
use App\Services\ScienerApi;

require __DIR__ . '/bootstrap.php';

if (!isset($argv)) {
    Logger::error('$argv has not been specified');
    exit;
}

try {
    switch ($argv[1]) {
        case 'expiredPasscodesRemover':
            $scienerApi = new ScienerApi();
            $removeExpiredPasscodes = new RemoveExpiredPasscodes($scienerApi);
            $removeExpiredPasscodes->execute();
            break;
        default:
            throw new Exception('run parameter not specified');
    }
} catch (\Exception $e) {
    Logger::error($e->getMessage());
}
