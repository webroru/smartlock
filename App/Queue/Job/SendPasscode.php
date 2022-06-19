<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Queue\Handlers\SendPasscodeHandler;

class SendPasscode extends AbstractJob
{
    public function __construct(private readonly int $bookingId)
    {
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
    }

    public function getHandlerFQCN(): string
    {
        return SendPasscodeHandler::class;
    }
}
