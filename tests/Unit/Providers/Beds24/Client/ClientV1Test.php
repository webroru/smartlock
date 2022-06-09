<?php

declare(strict_types=1);

namespace Unit\Providers\Beds24\Client;

use App\Providers\Beds24\Client\ClientV1;
use tests\App\Unit\UnitTestCase;

class ClientV1Test extends UnitTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testSetBooking()
    {
        $requestData = [
            'bookId' => 30641419,
            'notes' => "Code: 222",
            'infoItems' => [
               [
                   'code' => 'SMARTLOCK',
                   'text' => 'Passcode: 111',
               ]
            ],
        ];
        $beds24Api = $this->performTestMethod();
        $beds24Api->setPropKey('test');
        $beds24Api->setBooking($requestData);
    }

    private function performTestMethod(): ClientV1
    {
        return $this->getContainer()->get(ClientV1::class);
    }
}
