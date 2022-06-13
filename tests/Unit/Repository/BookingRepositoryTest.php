<?php

namespace Unit\Repository;

use App\Entity\Booking;
use App\Repository\BookingRepositoryInterface;
use tests\App\Unit\UnitTestCase;

class BookingRepositoryTest extends UnitTestCase
{
    private const NAME = 'test name';
    private const PHONE = '+7 (987) 654-32-10';
    private const ORDER_ID = 'test-123';
    private const PROPERTY_ID = '1111';

    private BookingRepositoryInterface $bookingRepository;
    private Booking $booking;

    protected function setUp(): void
    {
        $this->bookingRepository = $this->getContainer()->get(BookingRepositoryInterface::class);

        $checkInDate = new \DateTime('14:00 +1 year');
        $checkOutDate = new \DateTime('12:00 +1 year + 1 day');
        $this->booking = (new Booking())
            ->setName(self::NAME)
            ->setPhone(self::PHONE)
            ->setCheckInDate($checkInDate)
            ->setCheckOutDate($checkOutDate)
            ->setProperty(self::PROPERTY_ID)
            ->setOrderId(self::ORDER_ID);
    }

    public function testAdd()
    {
        $id = $this->bookingRepository->add($this->booking);
        $this->bookingRepository->delete($id);
        $this->assertIsInt($id);
    }

    public function testUpdate()
    {
        $id = $this->bookingRepository->add($this->booking);
        $this->booking->setId($id);
        $this->booking->setName('test');
        $this->bookingRepository->update($this->booking);
        $testBooking = $this->bookingRepository->find($id);
        $this->bookingRepository->delete($id);
        $this->assertEquals('test', $testBooking->getName());
    }

    public function testFind()
    {
        $id = $this->bookingRepository->add($this->booking);
        $testBooking = $this->bookingRepository->find($id);
        $this->bookingRepository->delete($id);
        $this->assertInstanceOf(Booking::class, ($testBooking));
    }

    public function testFindBy()
    {
        $id = $this->bookingRepository->add($this->booking);
        $testBookings = $this->bookingRepository->findBy(['id' => $id]);
        $this->bookingRepository->delete($id);
        $testBooking = $testBookings[0];
        $this->assertIsArray($testBookings);
        $this->assertInstanceOf(Booking::class, ($testBooking));
        $this->assertEquals($id, $testBooking->getId());
    }

    public function testDelete()
    {
        $id = $this->bookingRepository->add($this->booking);
        $this->bookingRepository->delete($id);
        $result = $this->bookingRepository->find($id);
        $this->assertNull($result);
    }
}
