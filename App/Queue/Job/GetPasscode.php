<?php

declare(strict_types=1);

namespace App\Queue\Job;

use App\Queue\Handlers\GetPasscodeHandler;

class GetPasscode extends AbstractJob
{
    public function __construct(
        private readonly int $bookingId,
        private readonly array $rooms,
        private readonly ?string $passcode = null,
    ) {
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
    }

    public function getRooms(): array
    {
        return $this->rooms;
    }

    public function getHandlerFQCN(): string
    {
        return GetPasscodeHandler::class;
    }

    public function getPasscode(): ?string
    {
        return $this->passcode;
    }
}
