<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Queue\Handlers\RemovePasscodeHandler;

class RemovePasscode extends AbstractJob
{
    public function __construct(
        private readonly int $lockId,
        private readonly bool $removeBooking = false,
    ) {
    }

    public function getLockId(): int
    {
        return $this->lockId;
    }

    public function removeBooking(): bool
    {
        return $this->removeBooking;
    }

    public function getHandlerFQCN(): string
    {
        return RemovePasscodeHandler::class;
    }
}
