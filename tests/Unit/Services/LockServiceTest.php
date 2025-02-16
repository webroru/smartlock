<?php

declare(strict_types=1);

namespace tests\App\Unit\Services;

use App\Entity\Booking;
use App\Entity\Lock;
use App\Entity\Room;
use App\Services\LockService;
use tests\App\Unit\UnitTestCase;

class LockServiceTest extends UnitTestCase
{
    private ?LockService $lockService;

    protected function setUp(): void
    {
        $this->lockService = $this->getContainer()->get(LockService::class);
    }

    public function testAddRandomPasscode()
    {
        $checkInDate = new \DateTime('14:00 +1 year');
        $checkOutDate = new \DateTime('12:00 +1 year + 1 day');

        $booking = (new Booking())
            ->setName('Test')
            ->setCheckInDate($checkInDate)
            ->setCheckOutDate($checkOutDate);

        $room = (new Room())
            ->setNumber('13')
            ->setLockId(6768288);

        $lock = $this->lockService->addRandomPasscode($booking, $room);
        $this->assertInstanceOf(Lock::class, $lock);
        $this->lockService->removePasscode($lock);
    }
}
