<?php

declare(strict_types=1);

namespace App\Commands;

use App\Logger;
use App\Queue\Job\GetPasscode;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\RoomRepositoryInterface;
use App\Services\BookingService;

/**
 * Adds new Booking and generate new passcode
 * E.g: $ php -f command.php booking:add Test 42 13 2023-06-24 2023-06-25 1
 */
class AddBooking
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly Dispatcher $dispatcher,
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

            $room = $this->roomRepository->findByNumber($data['room']);
            $mainRoom = $this->roomRepository->getMainRoom();

            if ($room) {
                $this->dispatcher->add(new GetPasscode($booking->getId(), $room->getId()));
            }

            $this->dispatcher->add(new GetPasscode($booking->getId(), $mainRoom->getId()));

            Logger::log("New GetPasscode Job added For {$booking->getName()} reservation");
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
    }
}
