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
use App\Repository\RoomRepositoryInterface;
use App\Services\LockService;

class GetPasscodeHandler implements HandlerInterface
{
    private const DELAY = 60;
    private const ATTEMPTS_LIMIT = 107; // 4 days
    private const CRITICAL_ATTEMPTS_LEVEL = 21; // 4 hours

    public function __construct(
        private readonly LockService $lockService,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly RoomRepositoryInterface $roomRepository,
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

            $rooms = $job->getRooms();
            $roomId = array_shift($rooms);
            $room = $this->roomRepository->find($roomId);
            if (!$room) {
                Logger::error("Can't find Room with Id: $roomId");
                return;
            }

            $passcode = $job->getPasscode();
            if ($passcode) {
                $lock = $this->lockService->addPasscode($booking, $room, $passcode);
            } else {
                $lock = $this->lockService->addRandomPasscode($booking, $room);
            }

            $lockId = $this->lockRepository->add($lock);
            Logger::log("For {$booking->getName()} have been added password: {$lock->getPasscode()}");

            if ($rooms) {
                $this->dispatcher->add(new GetPasscode($bookingId, $rooms, $lock->getPasscode()));
            } else {
                $this->dispatcher->add(new SendPasscode($lockId));
                Logger::log("New SendPasscode Job added For {$booking->getName()} reservation");
            }
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
