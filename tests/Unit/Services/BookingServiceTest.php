<?php

declare(strict_types=1);

namespace tests\App\Unit\Services;

use App\Entity\Booking;
use App\Services\BookingService;
use tests\App\Unit\UnitTestCase;

class BookingServiceTest extends UnitTestCase
{
    public function testUpdateCode()
    {
        $booking = (new Booking())
            ->setOrderId('111')
            ->setCode('222')
            ->setProperty('159459');
        $bookingService = $this->performTestMethod();
        $bookingService->updateCode($booking);
    }

    private function performTestMethod(): BookingService
    {
        return $this->getContainer()->get(BookingService::class);
    }
}
