<?php

declare(strict_types=1);

namespace App\Queue\Handlers;

use App\Logger;
use App\Queue\Job\JobInterface;
use App\Queue\Job\SendPasscode;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\LockRepositoryInterface;
use App\Services\BookingService;

class SendPasscodeHandler implements HandlerInterface
{
    private const DELAY = 5 * 60;
    private const ATTEMPTS_LIMIT = 10;

    public function __construct(
        private readonly BookingService $bookingService,
        private readonly LockRepositoryInterface $lockRepository,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /**
     * @param SendPasscode $job
     */
    public function handle(JobInterface $job): void
    {
        try {
            $lockId = $job->getLockId();
            $lock = $this->lockRepository->find($lockId);
            if (!$lock) {
                Logger::error("Can't find Lock by Id: $lockId");
                return;
            }

            $this->bookingService->updateCode($lock);
            Logger::log("Passcode {$lock->getPasscode()} has been sent for {$lock->getBooking()->getName()}");
        } catch (\Exception $e) {
            $error = "Couldn't register new passcode for the booking id {$job->getLockId()}. Error: {$e->getMessage()}.";
            if (++$job->attempts < self::ATTEMPTS_LIMIT) {
                $error .= " The Job has been added to the queue. Attempt № $job->attempts";
                $this->dispatcher->add($job, $job->attempts * self::DELAY);
            }
            Logger::error($error);
        }
    }
}
