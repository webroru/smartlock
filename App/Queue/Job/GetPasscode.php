<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Queue\Handlers\GetPasscodeHandler;

class GetPasscode extends AbstractJob
{
    public function __construct(private readonly int $bookingId, private readonly string $lockId)
    {
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
    }

    public function getLockId(): string
    {
        return $this->lockId;
    }

    public function getHandlerFQCN(): string
    {
        return GetPasscodeHandler::class;
    }
}
