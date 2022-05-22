<?php

namespace App\Commands;

use App\Logger;
use App\Services\LockService;

class RemoveExpiredPasscodes
{
    private LockService $lockService;

    public function __construct(
        LockService $lockService
    ) {
        $this->lockService = $lockService;
    }

    public function execute(): void
    {
        $this->lockService->removeExpiredPasscodes();
        Logger::log('Expired passcodes removed successfully');
    }
}
