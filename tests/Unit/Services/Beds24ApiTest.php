<?php

namespace Unit\Services;

use App\Services\Beds24Api;

use tests\App\Unit\UnitTestCase;


class Beds24ApiTest extends UnitTestCase
{
    public function testSetBooking()
    {
        $requestData = [
            'bookId' => 111,
            'custom1' => "Code: 222",
        ];
        $beds24Api = $this->performTestMethod();
        $beds24Api->setBooking($requestData);
    }

    private function performTestMethod(): Beds24Api
    {
        return $this->getContainer()->get(Beds24Api::class);
    }
}
