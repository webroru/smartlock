<?php

namespace Unit\Services;

use App\Services\Beds24Api;

use tests\App\Unit\UnitTestCase;


class Beds24ApiTest extends UnitTestCase
{
    public function testSetBooking()
    {
        $requestData = [
            'bookId' => 30641419,
            'notes' => "Code: 222",
            'infoItems' => [
               [
                   'code' => 'SMARTLOCK',
                   'text'=> 'Passcode: 111',
               ]
            ],
        ];
        $beds24Api = $this->performTestMethod();
        $beds24Api->setBooking($requestData);
    }

    private function performTestMethod(): Beds24Api
    {
        return $this->getContainer()->get(Beds24Api::class);
    }
}
