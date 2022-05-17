<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Providers\Beds24\Client\ClientV1;

class BookingService
{
    private const CODELOCK = 'CODELOCK';

    private ClientV1 $beds24Api;
    private array $beds24Props;

    public function __construct(ClientV1 $beds24Api, array $beds24Props)
    {
        $this->beds24Api = $beds24Api;
        $this->beds24Props = $beds24Props;
    }

    public function updateCode(Booking $booking): void
    {
        if (!$booking->getCode()) {
            throw new \Exception('The booking code is empty');
        }

        if (!$booking->getProperty()) {
            throw new \Exception('The booking property is empty');
        }

        $this->beds24Api->setPropKey($this->getPropKey($booking->getProperty()));
        $requestData = [
            'bookId' => $booking->getOrderId(),
            'infoItems' => [
                [
                    'code' => self::CODELOCK,
                    'text' => "Passcode: #{$booking->getCode()}#",
                ]
            ],
        ];
        $this->beds24Api->setBooking($requestData);
    }

    private function getPropKey(string $property): string
    {
        if (!isset($this->beds24Props[$property])) {
            throw new \Exception("Unknown property: $property");
        }

        return $this->beds24Props[$property];
    }
}
