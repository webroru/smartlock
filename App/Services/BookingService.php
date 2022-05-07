<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Providers\Beds24\Client\ClientV1;

class BookingService
{
    private const SMARTLOCK = 'SMARTLOCK';

    private ClientV1 $beds24Api;

    public function __construct(ClientV1 $beds24Api)
    {

        $this->beds24Api = $beds24Api;
    }

    public function updateCode(Booking $booking): void
    {
        if (!$booking->getCode()) {
            throw new \Exception('The booking code is empty');
        }

        $requestData = [
            'bookId' => $booking->getOrderId(),
            'infoItems' => [
                [
                    'code' => self::SMARTLOCK,
                    'text'=> "Passcode: {$booking->getCode()}",
                ]
            ],
        ];
        $this->beds24Api->setBooking($requestData);
    }
}