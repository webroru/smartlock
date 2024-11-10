<?php

declare(strict_types=1);

namespace App\Queue\Handlers;

use App\Logger;
use App\Queue\Job\ChangeLockEndDate;
use App\Queue\Job\JobInterface;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\LockRepositoryInterface;
use App\Services\LockService;

class ChangeLockEndDateHandler implements HandlerInterface
{
    private const DELAY = 60;
    private const ATTEMPTS_LIMIT = 107; // 4 days
    private const CRITICAL_ATTEMPTS_LEVEL = 21; // 4 hours

    public function __construct(
        private readonly LockService $lockService,
        private readonly LockRepositoryInterface $lockRepository,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /**
     * @param ChangeLockEndDate $job
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

            $this->lockService->updatePasscode($lock);
            Logger::log("For {$lock->getName()} ({$lock->getId()}) have been changed endDate: {$lock->getEndDate()->format('Y-m-d H:i:s')}");
        } catch (\Exception $e) {
            $error = "Couldn't register new passcode for the booking id {$job->getLockId()}. Error: {$e->getMessage()}.";
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
