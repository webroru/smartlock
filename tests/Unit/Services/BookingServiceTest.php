<?php

declare(strict_types=1);

namespace tests\App\Unit\Services;

use App\Entity\Booking;
use App\Entity\Lock;
use App\Services\BookingService;
use tests\App\Unit\UnitTestCase;

class BookingServiceTest extends UnitTestCase
{
    private ?BookingService $bookingService;

    protected function setUp(): void
    {
        $this->bookingService = $this->getContainer()->get(BookingService::class);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testUpdateCode()
    {
        $booking = (new Booking())
            ->setOrderId('30641419')
            ->setProperty('159459');

        $lock = (new Lock())->setPasscode('222')
            ->setBooking($booking);

        $this->bookingService->updateCode($lock);
    }

    public function testCreate()
    {
        $data = [
            'checkindate' => (new \DateTime('Today'))->format('Y-m-d h:i:s'),
            'checkoutdate' => (new \DateTime('+1 day'))->format('Y-m-d h:i:s'),
            'guestname' => 'Test',
            'order_id' => '1000',
            'property' => '1000',
        ];

        $booking = $this->bookingService->create($data);
        $this->assertInstanceOf(Booking::class, $booking);
    }
}
