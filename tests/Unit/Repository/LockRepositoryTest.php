<?php

declare(strict_types=1);

namespace tests\App\Unit\Repository;

use App\Entity\Booking;
use App\Entity\Lock;
use App\Entity\Room;
use App\Repository\BookingRepositoryInterface;
use App\Repository\LockRepositoryInterface;
use App\Repository\RoomRepositoryInterface;
use tests\App\Unit\UnitTestCase;

class LockRepositoryTest extends UnitTestCase
{
    private Lock $lock;
    private LockRepositoryInterface $lockRepository;
    private RoomRepositoryInterface $roomRepository;
    private BookingRepositoryInterface $bookingRepository;
    private Room $room;
    private Booking $booking;

    protected function setUp(): void
    {
        $this->lockRepository = $this->getContainer()->get(LockRepositoryInterface::class);
        $this->roomRepository = $this->getContainer()->get(RoomRepositoryInterface::class);
        $this->bookingRepository = $this->getContainer()->get(BookingRepositoryInterface::class);

        $this->room = (new Room())
        ->setLockId('test')
            ->setNumber('test');

        $roomId = $this->roomRepository->add($this->room);
        $this->room->setId($roomId);

        $this->booking = (new Booking())
            ->setName('test')
            ->setCheckInDate(new \DateTime())
            ->setCheckOutDate(new \DateTime('+1 year'))
            ->setProperty('test')
            ->setOrderId('test');

        $bookingId = $this->bookingRepository->add($this->booking);
        $this->booking->setId($bookingId);

        $this->lock = (new Lock())
            ->setName('test')
            ->setPasscode('0000')
            ->setPasscodeId(42)
            ->setStartDate(new \DateTime())
            ->setEndDate(new \DateTime('+1 year'))
            ->setBooking($this->booking)
            ->setRoom($this->room);
    }


    protected function tearDown(): void
    {
        $this->roomRepository->delete($this->room->getId());
        $this->bookingRepository->delete($this->booking->getId());
    }

    public function testAdd()
    {
        $id = $this->lockRepository->add($this->lock);
        $this->lockRepository->delete($id);
        $this->assertIsInt($id);
    }

    public function testUpdate()
    {
        $id = $this->lockRepository->add($this->lock);
        $this->lock->setId($id);
        $this->lock->setPasscode('1111');
        $this->lock->setDeleted(true);
        $this->lockRepository->update($this->lock);
        $lock = $this->lockRepository->find($id);
        $this->lockRepository->delete($id);
        $this->assertEquals('1111', $lock->getPasscode());
        $this->assertEquals(true, $lock->getDeleted());
    }

    public function testFind()
    {
        $id = $this->lockRepository->add($this->lock);
        $lock = $this->lockRepository->find($id);
        $this->lockRepository->delete($id);
        $this->assertInstanceOf(Lock::class, ($lock));
    }

    public function testFindBy()
    {
        $id = $this->lockRepository->add($this->lock);
        $locks = $this->lockRepository->findBy(['id' => $id]);
        $this->lockRepository->delete($id);
        $lock = $locks[0];
        $this->assertIsArray($locks);
        $this->assertInstanceOf(Lock::class, ($lock));
        $this->assertEquals($id, $lock->getId());
    }

    public function testDelete()
    {
        $id = $this->lockRepository->add($this->lock);
        $this->lockRepository->delete($id);
        $result = $this->lockRepository->find($id);
        $this->assertNull($result);
    }
}
