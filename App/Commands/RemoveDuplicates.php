<?php

namespace App\Commands;

use App\Logger;
use App\Services\LockService;

class RemoveDuplicates
{
    private LockService $lockService;

    public function __construct(
        LockService $lockService
    ) {
        $this->lockService = $lockService;
    }

    public function execute(): void
    {
        $this->lockService->removeDuplicates();
        Logger::log('Duplicated passcodes removed successfully');
    }
}
