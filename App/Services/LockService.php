<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Logger;

class LockService
{
    private $scienerApi;

    public function __construct(
        ScienerApi $scienerApi
    ) {
        $this->scienerApi = $scienerApi;
    }

    public function getPassword(Booking $booking): string
    {
        try {
            $password = $this->scienerApi->addRandomPasscode(
                $booking->getName(),
                $booking->getCheckInDate()->getTimestamp() * 1000,
                $booking->getCheckOutDate()->getTimestamp() * 1000
            );
        } catch (\Exception $e) {
            throw new \Exception("Adding a passcode to the Lock is failed. Reason: {$e->getMessage()}");
        }

        Logger::log(
            "For {$booking->getName()} have been added password: {$password} valid from " .
            "{$booking->getCheckInDate()->format('Y-m-d H:i')} " .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}"
        );

        return $password;
    }
}
