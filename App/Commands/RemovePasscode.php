<?php

declare(strict_types=1);

namespace App\Commands;

use App\Entity\Booking;
use App\Logger;
use App\Queue\Job\RemovePasscode as RemovePasscodeJob;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\BookingRepositoryInterface;
use App\Repository\LockRepositoryInterface;

/**
 * Removes Passcode by Booking Order ID. Add 'remove_booking' to remove Booking entity
 * E.g: $ php -f command.php passcode:remove 42
 */
class RemovePasscode
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly LockRepositoryInterface $lockRepository,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function execute(array $params): void
    {
        if (!isset($params[0])) {
            $error = 'Error: Booking Order Id should be declared';
            Logger::error($error);
            exit($error);
        }
        $orderId = $params[0];
        $removeBooking = isset($params[1]) && $params[1] === 'remove_booking';

        try {
            /** @var Booking[] $bookings */
            $bookings = $this->bookingRepository->findBy(['order_id' => $orderId]);
            foreach ($bookings as $booking) {
                $this->addJobs($booking, $removeBooking);
            }
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            exit("Error: {$e->getMessage()}");
        }
    }

    private function addJobs(Booking $booking, $removeBooking): void
    {
        $locks = $this->lockRepository->findBy(['booking_id' => $booking->getId()]);
        foreach ($locks as $lock) {
            $this->dispatcher->add(new RemovePasscodeJob($lock->getId(), $removeBooking));
            Logger::log("New RemovePasscodeJob Job added For {$booking->getName()} (id {$booking->getId()})");
        }
    }
}
