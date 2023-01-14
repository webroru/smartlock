<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Entity\Lock;
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

    public function create(array $data): Booking
    {
        $checkInDate = $data['checkindate'] ?? null;
        $checkOutDate = $data['checkoutdate'] ?? null;
        $guestName = $data['guestname'] ?? 'Guest';
        $phone = $data['phone'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $property = $data['property'] ?? null;

        if (!$checkInDate || !$checkOutDate || !$guestName || !$orderId || !$property) {
            throw new \Exception('Data is not valid:' . implode(', ', $data));
        }

        return (new Booking())
            ->setName($guestName)
            ->setPhone($phone)
            ->setCheckInDate($this->prepareCheckinDate($checkInDate))
            ->setCheckOutDate($this->prepareCheckoutDate($checkOutDate))
            ->setOrderId($orderId)
            ->setProperty($property);
    }

    public function updateCode(Lock $lock): void
    {
        if (!$lock->getBooking()->getProperty()) {
            throw new \Exception('The booking property is empty');
        }

        $this->beds24Api->setPropKey($this->getPropKey($lock->getBooking()->getProperty()));
        $requestData = [
            'bookId' => $lock->getBooking()->getOrderId(),
            'infoItems' => [
                [
                    'code' => self::CODELOCK,
                    'text' => "Passcode: #{$lock->getPasscode()}#",
                ]
            ],
        ];
        $this->beds24Api->setBooking($requestData);
    }

    public function updatePhone(Booking $booking): void
    {
        if (!$booking->getProperty()) {
            throw new \Exception('The booking property is empty');
        }

        if (!$booking->getPhone()) {
            throw new \Exception('The booking phone is empty');
        }

        $this->beds24Api->setPropKey($this->getPropKey($booking->getProperty()));
        $requestData = [
            'bookId' => $booking->getOrderId(),
            'guestPhone' => $booking->getPhone(),
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

    private function prepareCheckinDate(string $date): \DateTime
    {
        return (new \DateTime($date, new \DateTimeZone('Europe/Prague')))->modify('13:00');
    }

    private function prepareCheckoutDate(string $date): \DateTime
    {
        return (new \DateTime($date, new \DateTimeZone('Europe/Prague')))->modify('11:00');
    }
}
