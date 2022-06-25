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

class RemovePasscodeHandler implements HandlerInterface
{
    private const DELAY = 5 * 60;
    private const ATTEMPTS_LIMIT = 10;

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
        try {
            $bookingId = $job->getBookingId();
            $booking = $this->bookingRepository->find($bookingId);
            if (!$booking) {
                Logger::error("Can't find Booking with Id: $bookingId");
                return;
            }

            $this->lockService->removePasscode($booking);
            $lock = $booking->getLock();
            $booking->setLock(null);
            $this->bookingRepository->update($booking);
            $this->lockRepository->delete($lock->getId());
            Logger::log("For {$booking->getName()} have been removed passcode: {$lock->getPasscode()}");
        } catch (\Exception $e) {
            $error = "Couldn't remove passcode for the booking id {$job->getBookingId()}. Error: {$e->getMessage()}.";
            if (++$job->attempts < self::ATTEMPTS_LIMIT) {
                $error .= " The Job has been added to the queue. Attempt № $job->attempts";
                $this->dispatcher->add($job, $job->attempts * self::DELAY);
            }
            Logger::error($error);
        }
    }
}
