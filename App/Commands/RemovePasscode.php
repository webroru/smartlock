<?php

declare(strict_types=1);

namespace App\Commands;

use App\Entity\Booking;
use App\Logger;
use App\Queue\Job\RemovePasscode as RemovePasscodeJob;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\BookingRepositoryInterface;

/**
 * Removes Passcode by Booking Order ID
 * E.g: $ php -f command.php passcode:remove 42
 */
class RemovePasscode
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
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

        try {
            /** @var Booking[] $bookings */
            $bookings = $this->bookingRepository->findBy(['order_id' => $orderId]);
            foreach ($bookings as $booking) {
                $this->dispatcher->add(new RemovePasscodeJob($booking->getId()));
                Logger::log("New RemovePasscodeJob Job added For {$booking->getName()} (id {$booking->getId()})");
            }
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            exit("Error: {$e->getMessage()}");
        }
    }
}
