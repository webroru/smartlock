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
                Logger::log("Lock removed:
                    'id' => {$lock->getId()},
                    'name' => {$lock->getName()},
                    'passcode_id' => {$lock->getPasscodeId()},
                    'passcode' => {$lock->getPasscode()},
                    'start_date' => {$lock->getStartDate()->format('Y-m-d H:i:s')},
                    'end_date' => {$lock->getEndDate()->format('Y-m-d H:i:s')},
                    'booking_id' => {$lock->getBooking()->getId()},
                    'room_id' => {$lock->getRoom()->getId()},
                    'deleted' => {$lock->getDeleted()} ,
                ]);");
                $lock->setDeleted(true);
                $this->lockRepository->update($lock);
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
        $passcode = sprintf('%06d', mt_rand(0, 9999));
        return $this->addPasscode($booking, $room, $passcode);
    }

    public function addPasscode(Booking $booking, Room $room, string $passcode): Lock
    {
        $name = $this->prepareName($booking->getName());
        $startDate = $booking->getCheckInDate()->getTimestamp() * 1000;
        $endDate = $booking->getCheckOutDate()->getTimestamp() * 1000;
        $lockId = $room->getLockId();
        $passcodeId = $this->scienerApi->addPasscode($name, $passcode, $startDate, $endDate, $lockId);
        return (new Lock())
            ->setName($name)
            ->setStartDate($booking->getCheckInDate())
            ->setEndDate($booking->getCheckOutDate())
            ->setPasscode($passcode)
            ->setPasscodeId($passcodeId)
            ->setBooking($booking)
            ->setRoom($room);
    }

    private function prepareName(string $name): string
    {
        return implode(' ', array_slice(explode(' ', $name), 0, 2));
    }
}
