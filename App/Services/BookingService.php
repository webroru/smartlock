<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Entity\Lock;
use App\Providers\Beds24\Client\ClientV1;
use App\Queue\Job\GetPasscode;
use App\Queue\RabbitMQ\Dispatcher;

class BookingService
{
    private const CODELOCK = 'CODELOCK';

    private ClientV1 $beds24Api;
    private array $beds24Props;
    private array $locks;
    private Dispatcher $dispatcher;

    public function __construct(ClientV1 $beds24Api, Dispatcher $dispatcher, array $beds24Props, array $locks)
    {
        $this->beds24Api = $beds24Api;
        $this->dispatcher = $dispatcher;
        $this->beds24Props = $beds24Props;
        $this->locks = $locks;
    }

    public function create(array $data): Booking
    {
        $checkInDate = $data['checkindate'] ?? null;
        $checkOutDate = $data['checkoutdate'] ?? null;
        $guestName = $data['guestname'] ?? null;
        $phone = $data['phone'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $property = $data['property'] ?? null;

        if (!$checkInDate || !$checkOutDate || !$guestName || !$orderId || !$property) {
            throw new \Exception("Data is not valid: $data");
        }

        return (new Booking())
            ->setName($guestName)
            ->setPhone($phone)
            ->setCheckInDate($this->prepareCheckinDate($checkInDate))
            ->setCheckOutDate($this->prepareCheckoutDate($checkOutDate))
            ->setOrderId($orderId)
            ->setProperty($property);
    }

    public function updateCode(Booking $booking): void
    {
        if (!$booking->getLock()?->getPasscode()) {
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
                    'text' => "Passcode: #{$booking->getLock()?->getPasscode()}#",
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
        return (new \DateTime($date))->modify('12:00');
    }

    private function prepareCheckoutDate(string $date): \DateTime
    {
        return (new \DateTime($date))->modify('10:00');
    }
}
