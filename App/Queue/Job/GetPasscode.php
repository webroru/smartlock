<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Queue\Handlers\GetPasscodeHandler;

class GetPasscode extends AbstractJob
{
    public function __construct(private readonly int $bookingId, private readonly int $roomId)
    {
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getHandlerFQCN(): string
    {
        return GetPasscodeHandler::class;
    }
}
