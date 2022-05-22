<?php

namespace Unit\Repository;

use App\Entity\Booking;
use App\Repository\BookingMysqlBookingRepository;
use tests\App\Unit\UnitTestCase;

class BookingRepositoryTest extends UnitTestCase
{
    private const NAME = 'test name';
    private const MAIL = 'test@mail.net';
    private const ORDER_ID = 'test-123';

    private BookingMysqlBookingRepository $bookingRepository;
    private Booking $booking;

    protected function setUp(): void
    {
        $this->bookingRepository = $this->getContainer()->get(BookingMysqlBookingRepository::class);

        $checkInDate = new \DateTime('14:00 +1 year');
        $checkOutDate = new \DateTime('12:00 +1 year + 1 day');
        $this->booking = (new Booking())
            ->setName(self::NAME)
            ->setEmail(self::MAIL)
            ->setCheckInDate($checkInDate)
            ->setCheckOutDate($checkOutDate)
            ->setOrderId(self::ORDER_ID);
    }

    public function testAdd()
    {
        $id = $this->bookingRepository->add($this->booking);
        $this->bookingRepository->delete($id);
        $this->assertIsString($id);
    }

    public function testGetUnregisteredBookingsByDateRange()
    {
        $id = $this->bookingRepository->add($this->booking);
        $date = new \DateTime('+1 year');
        /** @var Booking[] $bookings */
        $bookings = $this->bookingRepository->getUnregisteredBookingsByDateRange($date);
        $testBooking = null;
        foreach ($bookings as $booking) {
            if ($booking->getName() === self::NAME && $booking->getEmail() === self::MAIL) {
                $testBooking = $booking;
                break;
            }
        }
        $this->bookingRepository->delete($id);
        $this->assertInstanceOf(Booking::class, ($testBooking));
    }

    public function testUpdate()
    {
        $id = $this->bookingRepository->add($this->booking);
        $this->booking->setId($id);
        $this->booking->setCode('test');
        $this->bookingRepository->update($this->booking);
        $testBooking = $this->bookingRepository->find($id);
        $this->bookingRepository->delete($id);
        $this->assertEquals('test', $testBooking->getCode());
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
