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
    private const DELAY = 60;
    private const ATTEMPTS_LIMIT = 107; // 4 days
    private const CRITICAL_ATTEMPTS_LEVEL = 21; // 4 hours

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

            $lock = $this->lockService->addRandomPasscode($booking);
            $lockId = $this->lockRepository->add($lock);
            $lock->setId($lockId);
            $booking->setLock($lock);
            $this->bookingRepository->update($booking);
            Logger::log("For {$booking->getName()} have been added password: {$lock->getPasscode()}");
            $this->dispatcher->add(new SendPasscode($bookingId));
            Logger::log("New SendPasscode Job added For {$booking->getName()} reservation");
        } catch (\Exception $e) {
            $error = "Couldn't register new passcode for the booking id {$job->getBookingId()}. Error: {$e->getMessage()}.";
            if (++$job->attempts < self::ATTEMPTS_LIMIT) {
                $error .= " The Job has been added to the queue. Attempt № $job->attempts";
                $this->dispatcher->add($job, $job->attempts * self::DELAY);
            }
            if ($job->attempts < self::CRITICAL_ATTEMPTS_LEVEL) {
                Logger::error($error);
            } else {
                Logger::critical($error);
            }
        }
    }
}
