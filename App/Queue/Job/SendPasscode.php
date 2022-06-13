<?php

declare(strict_types=1);

namespace App\Queue\Job;

class SendPasscode implements JobInterface
{
    public function __construct(private readonly int $bookingId)
    {
    }

    public function getBookingId(): int
    {
        return $this->bookingId;
    }
}
