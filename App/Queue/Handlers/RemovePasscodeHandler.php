<?php

declare(strict_types=1);

namespace App\Queue\Handlers;

use App\Logger;
use App\Queue\Job\JobInterface;
use App\Queue\Job\RemovePasscode;
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
     * @param RemovePasscode $job
     */
    public function handle(JobInterface $job): void
    {
        try {
            $lockId = $job->getLockId();
            $lock = $this->lockRepository->find($lockId);
            if (!$lock) {
                Logger::error("Can't find Lock with Id: $lockId");
                return;
            }

            $this->lockService->removePasscode($lock);
            $this->lockRepository->delete($lock->getId());
            Logger::log("For {$lock->getBooking()->getName()} has been removed passcode: {$lock->getPasscode()}");

            $locks = $this->lockRepository->findBy(['booking_id' => $lock->getBooking()->getId()]);

            if ($job->removeBooking() && count($locks) === 0) {
                $this->bookingRepository->delete($lock->getBooking()->getId());
                Logger::log("Booking Order Id {$lock->getBooking()->getOrderId()} has been removed");
            }
        } catch (\Exception $e) {
            $error = "Couldn't remove passcode for the lock id {$job->getLockId()}. Error: {$e->getMessage()}.";
            if (++$job->attempts < self::ATTEMPTS_LIMIT) {
                $error .= " The Job has been added to the queue. Attempt № $job->attempts";
                $this->dispatcher->add($job, $job->attempts * self::DELAY);
            }
            Logger::error($error);
        }
    }
}
