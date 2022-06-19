<?php

declare(strict_types=1);

namespace App\Queue\Handlers;

use App\Logger;
use App\Queue\Job\GetPasscode;
use App\Queue\Job\JobInterface;
use App\Queue\Job\SendPasscode;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\BookingRepositoryInterface;
use App\Repository\LockRepositoryInterface;
use App\Services\LockService;

class GetPasscodeHandler implements HandlerInterface
{
    public function __construct(
        private readonly LockService $lockService,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly LockRepositoryInterface $lockRepository,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /**
     * @param GetPasscode $job
     */
    public function handle(JobInterface $job): void
    {
        $bookingId = $job->getBookingId();
        $booking = $this->bookingRepository->find($bookingId);
        if (!$booking) {
            Logger::error("Can't find Booking with Id: $bookingId");
            return;
        }

        try {
            $lock = $this->lockService->addRandomPasscode($booking);
            $lockId = $this->lockRepository->add($lock);
            $lock->setId($lockId);
            $booking->setLock($lock);
            $this->bookingRepository->update($booking);
            Logger::log("For {$booking->getName()} have been added password: {$lock->getPasscode()}");
            $this->dispatcher->add(new SendPasscode($bookingId));
            Logger::log("New SendPasscode Job added For {$booking->getName()} reservation");
        } catch (\Exception $e) {
            $error = "Couldn't register new passcode for the booking. Error: {$e->getMessage()}. " .
                "Guest: {$booking->getName()}, " .
                "Reservation: {$booking->getCheckInDate()->format('Y-m-d H:i')} — " .
                "{$booking->getCheckOutDate()->format('Y-m-d H:i')}, " .
                "Order №: {$booking->getOrderId()}.";

            Logger::error($error);

            if (--$job->attempts > 0) {
                $this->dispatcher->add($job);
            }
        }
    }
}
