<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Entity\Lock;
use App\Entity\Room;
use App\Logger;
use App\Providers\Sciener\Client\Client;
use App\Repository\LockRepositoryInterface;
use App\Repository\RoomRepositoryInterface;

class LockService
{
    private const IGNORE_PASSCODES = [37782310, 37780116, 37663144, 37663134, 9318334];

    private Client $scienerApi;
    private LockRepositoryInterface $lockRepository;
    private RoomRepositoryInterface $roomRepository;

    public function __construct(
        Client $scienerApi,
        LockRepositoryInterface $lockRepository,
        RoomRepositoryInterface $roomRepository,
    ) {
        $this->scienerApi = $scienerApi;
        $this->lockRepository = $lockRepository;
        $this->roomRepository = $roomRepository;
    }

    public function removeExpiredPasscodes(): void
    {
        /** @var Lock $lock */
        foreach ($this->lockRepository->getExpired() as $lock) {
            if (in_array($lock->getPasscodeId(), self::IGNORE_PASSCODES)) {
                continue;
            }

            try {
                $this->scienerApi->deletePasscode($lock->getPasscodeId(), $lock->getRoom()->getLockId());
            } catch (\Exception $e) {
                Logger::error("{$e->getMessage()}");
            }
        }
    }

    public function removeDuplicates(): void
    {
        /** @var Room $room */
        foreach ($this->roomRepository->getAll() as $room) {
            $passCodes = $this->scienerApi->getAllPasscodes($room->getLockId());
            $groupedByNameAndDate = [];

            foreach ($passCodes as $passCode) {
                if (!isset($passCode['keyboardPwdName'])) {
                    continue;
                }

                $key = $passCode['keyboardPwdName'] .
                    '_' . $passCode['startDate'] .
                    '_' . $passCode['endDate'];

                if (!isset($groupedByNameAndDate[$key]['maxId'])) {
                    $groupedByNameAndDate[$key]['maxId'] = $passCode['keyboardPwdId'];
                } else {
                    if ($groupedByNameAndDate[$key]['maxId'] > $passCode['keyboardPwdId']) {
                        $groupedByNameAndDate[$key]['ids'][] = $passCode['keyboardPwdId'];
                    } else {
                        $groupedByNameAndDate[$key]['ids'][] = $groupedByNameAndDate[$key]['maxId'];
                        $groupedByNameAndDate[$key]['maxId'] = $passCode['keyboardPwdId'];
                    }
                }
            }

            $ids = [];
            foreach ($groupedByNameAndDate as $item) {
                if (isset($item['ids']) && count($item['ids']) > 1) {
                    $ids = array_merge($ids, $item['ids']);
                }
            }

            foreach ($ids as $id) {
                try {
                    $this->scienerApi->deletePasscode($id, $room->getLockId());
                } catch (\Exception $e) {
                    Logger::error("{$e->getMessage()}");
                }
            }
        }
    }

    public function removePasscode(Lock $lock): void
    {
        $this->scienerApi->deletePasscode($lock->getPasscodeId(), $lock->getRoom()->getLockId());
    }

    public function addRandomPasscode(Booking $booking, Room $room): Lock
    {
        $name = $this->prepareName($booking->getName());
        $startDate = $booking->getCheckInDate()->getTimestamp() * 1000;
        $endDate = $booking->getCheckOutDate()->getTimestamp() * 1000;
        $password = sprintf('%04d', mt_rand(0, 9999));
        $lockId = $room->getLockId();
        $passcodeId = $this->scienerApi->addPasscode($name, $password, $startDate, $endDate, $lockId);
        return (new Lock())
            ->setName($name)
            ->setStartDate($booking->getCheckInDate())
            ->setEndDate($booking->getCheckOutDate())
            ->setPasscode($password)
            ->setPasscodeId($passcodeId)
            ->setBooking($booking)
            ->setRoom($room);
    }

    private function prepareName(string $name): string
    {
        return implode(' ', array_slice(explode(' ', $name), 0, 2));
    }
}
