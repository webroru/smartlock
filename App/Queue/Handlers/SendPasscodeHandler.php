<?php

declare(strict_types=1);

namespace App\Queue\Handlers;

use App\Logger;
use App\Queue\Job\JobInterface;
use App\Queue\Job\SendPasscode;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\BookingRepositoryInterface;
use App\Services\BookingService;

class SendPasscodeHandler implements HandlerInterface
{
    private const DELAY = 5 * 60;
    private const ATTEMPTS_LIMIT = 10;

    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /**
     * @param SendPasscode $job
     */
    public function handle(JobInterface $job): void
    {
        try {
            $bookingId = $job->getBookingId();
            $booking = $this->bookingRepository->find($bookingId);
            if (!$booking) {
                Logger::error("Can't find Booking with Id: $bookingId");
                return;
            }

            if (!$booking->getLock()) {
                Logger::error("Can't get the passcode from the Booking with Id: $bookingId");
                return;
            }

            $this->bookingService->updateCode($booking);
            Logger::log("Passcode {$booking->getLock()->getPasscode()} has been sent for {$booking->getName()}");
        } catch (\Exception $e) {
            $error = "Couldn't register new passcode for the booking id {$job->getBookingId()}. Error: {$e->getMessage()}.";
            if (++$job->attempts < self::ATTEMPTS_LIMIT) {
                $error .= " The Job has been added to the queue. Attempt № $job->attempts";
                $this->dispatcher->add($job, $job->attempts * self::DELAY);
            }
            Logger::error($error);
        }
    }
}
