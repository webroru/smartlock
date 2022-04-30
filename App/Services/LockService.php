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

    public function registerBooking(Booking $booking): void
    {
        $password = $this->scienerApi->addRandomPasscode(
            $booking->getName(),
            $booking->getCheckInDate()->getTimestamp() * 1000,
            $booking->getCheckOutDate()->getTimestamp() * 1000
        );
        if (!$password) {
            $error = "Can't add passcode. All attempts have been spent." .
                "Guest: {$booking->getName()}, " .
                "Reservation: {$booking->getCheckInDate()->format('Y-m-d H:i')} — " .
                "{$booking->getCheckOutDate()->format('Y-m-d H:i')}, " .
                "Order №: {$booking->getOrderId()}, " .
                "Mail: {$booking->getEmail()}";
            throw new \Exception($error);
        }
        $booking->setCode($password);

        //$this->sendMail($booking, $isChanged);
        Logger::log(
            "For {$booking->getName()} have been added password: {$booking->getCode()} valid from " .
            "{$booking->getCheckInDate()->format('Y-m-d H:i')} " .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}"
        );
    }
}
