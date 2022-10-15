<?php

declare(strict_types=1);

namespace App\Commands;

use App\Logger;
use App\Services\BookingService;

/**
 * Adds new Booking and generate new passcode
 * E.g: $ php -f command.php booking:add Test 42 13 2023-06-24 2023-06-25 1
 */
class AddBooking
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {
    }

    public function execute(array $params): void
    {
        $data = [
            'guestname' => $params[0] ?? null,
            'order_id' => $params[1] ?? null,
            'property' => $params[2] ?? null,
            'checkindate' => $params[3] ?? null,
            'checkoutdate' => $params[4] ?? null,
            'room' => $params[5] ?? null,
            'phone' => $params[5] ?? null,
        ];

        try {
            $booking = $this->bookingService->create($data);
            $this->bookingService->queueGettingPassCode($booking);
            Logger::log("New GetPasscode Job added For {$booking->getName()} reservation");
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
    }
}
