<?php

namespace tests\App\Unit;

use App\Entity\Booking;
use App\Repository\BookingFirestoreRepository;
use PHPUnit\Framework\TestCase;

class BookingFirestoreRepositoryTest extends TestCase
{
    private const NAME = 'test name';
    private const MAIL = 'test@mail.net';
    private const ORDER_ID = 'test-123';

    /** @var BookingFirestoreRepository */
    private $bookingRepository;
    /** @var Booking */
    private $testBooking;

    protected function setUp(): void
    {
        \Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
        $this->bookingRepository = new BookingFirestoreRepository();

        $checkInDate = new \DateTime('14:00 +1 year');
        $checkOutDate = new \DateTime('12:00 +1 year + 1 day');
        $this->testBooking = (new Booking())
            ->setName(self::NAME)
            ->setEmail(self::MAIL)
            ->setCheckInDate($checkInDate)
            ->setCheckOutDate($checkOutDate)
            ->setOrderId(self::ORDER_ID);
    }

    public function testAdd()
    {
        $id = $this->bookingRepository->add($this->testBooking);
        $this->bookingRepository->delete($id);
        $this->assertIsString($id);
    }

    public function testGetUnregisteredBookingsByDateRange()
    {
        $id = $this->bookingRepository->add($this->testBooking);
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
        $id = $this->bookingRepository->add($this->testBooking);
        $this->testBooking->setId($id);
        $this->testBooking->setCode('test');
        $this->bookingRepository->update($this->testBooking);
        $testBooking = $this->bookingRepository->find($id);
        $this->bookingRepository->delete($id);
        $this->assertEquals('test', $testBooking->getCode());
    }

    public function testFind()
    {
        $id = $this->bookingRepository->add($this->testBooking);
        $testBooking = $this->bookingRepository->find($id);
        $this->bookingRepository->delete($id);
        $this->assertInstanceOf(Booking::class, ($testBooking));
    }

    public function testFindBy()
    {
        $id = $this->bookingRepository->add($this->testBooking);
        $testBookings = $this->bookingRepository->findBy(['id' => $id]);
        $this->bookingRepository->delete($id);
        $testBooking = $testBookings[0];
        $this->assertIsArray($testBookings);
        $this->assertInstanceOf(Booking::class, ($testBooking));
        $this->assertEquals($id, $testBooking->getId());
    }

    public function testDelete()
    {
        $id = $this->bookingRepository->add($this->testBooking);
        $this->bookingRepository->delete($id);
        $result = $this->bookingRepository->find($id);
        $this->assertNull($result);
    }
}
