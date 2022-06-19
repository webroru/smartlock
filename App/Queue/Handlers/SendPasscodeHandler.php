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

        try {
            $this->bookingService->updateCode($booking);
            Logger::log("Passcode {$booking->getLock()->getPasscode()} has been sent for {$booking->getName()}");
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
