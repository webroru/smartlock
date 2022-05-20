<?php

declare(strict_types=1);

namespace tests\App\Unit\Services;

use App\Entity\Booking;
use App\Providers\Sciener\Client\Client;
use App\Services\BookingService;
use tests\App\Unit\UnitTestCase;

class BookingServiceTest extends UnitTestCase
{
    private ?BookingService $bookingService;

    protected function setUp(): void
    {
        $this->bookingService = $this->getContainer()->get(BookingService::class);
    }

    public function testUpdateCode()
    {
        $booking = (new Booking())
            ->setOrderId('30641419')
            ->setCode('222')
            ->setProperty('159459');
        $this->bookingService->updateCode($booking);
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
