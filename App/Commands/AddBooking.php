<?php

declare(strict_types=1);

namespace App\Commands;

use App\Logger;
use App\Queue\Job\GetPasscode;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\BookingRepositoryInterface;
use App\Repository\RoomRepositoryInterface;
use App\Services\BookingService;

/**
 * Adds new Booking and generate new passcode
 * E.g: $ php -f command.php booking:add Test 42 13 2023-06-24 2023-06-25 111 123
 */
class AddBooking
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly RoomRepositoryInterface $roomRepository,
        private readonly BookingRepositoryInterface $bookingRepository,
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
            'phone' => $params[6] ?? null,
        ];

        try {
            $booking = $this->bookingService->create($data);
            $bookingId = $this->bookingRepository->add($booking);
            $room = $this->roomRepository->findByNumber($data['room']);
            $mainRoom = $this->roomRepository->getMainRoom();

            $rooms = [$mainRoom->getId()];
            if ($room) {
                $rooms[] = $room->getId();
            }

            $this->dispatcher->add(new GetPasscode($bookingId, $rooms));

            Logger::log("New GetPasscode Job added For {$booking->getName()} reservation");
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
    }
}
