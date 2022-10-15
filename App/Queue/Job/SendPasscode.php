<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Queue\Handlers\SendPasscodeHandler;

class SendPasscode extends AbstractJob
{
    public function __construct(private readonly int $lockId)
    {
    }

    public function getLockId(): int
    {
        return $this->lockId;
    }

    public function getHandlerFQCN(): string
    {
        return SendPasscodeHandler::class;
    }
}
