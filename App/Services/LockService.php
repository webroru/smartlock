<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Logger;
use App\Repository\RepositoryInterface;
use App\ScienerApi;

class LockService
{
    private const REGISTRATION_DELAY = 60 * 60 * 24 * 7; // 1 week

    private $scienerApi;
    private $bookingRepository;

    public function __construct(
        ScienerApi $scienerApi,
        RepositoryInterface $bookingRepository
    ) {
        $this->scienerApi = $scienerApi;
        $this->bookingRepository = $bookingRepository;
    }

    public function registerBooking(Booking $booking): void
    {
        if ($booking->getCheckInDate()->getTimestamp() - time() > self::REGISTRATION_DELAY) {
            $this->lockService->registerBooking($booking);
        }
        $password = $this->scienerApi->addRandomPasscode(
            $booking->getName(),
            $booking->getCheckInDate()->getTimestamp() * 1000,
            $booking->getCheckOutDate()->getTimestamp() * 1000
        );
        if (!$password) {
            $error = "Can't add passcode. All attempts have been spent." .
                "Guest: {$booking->getName()}, " .
                "Reservation: {$booking->getCheckInDate()->format('Y-m-d H:i')} — " .
                "{$booking->getCheckOutDate()->format('Y-m-d H:i')}, " .
                "Order №: {$booking->getOrderId()}, " .
                "Mail: {$booking->getEmail()}";
            throw new \Exception($error);
        }
        $booking->setCode($password);
        $this->bookingRepository->update($booking);

        //$this->sendMail($booking, $isChanged);
        Logger::log(
            "For {$booking->getName()} have been added password: {$booking->getCode()} valid from " .
            "{$booking->getCheckInDate()->format('Y-m-d H:i')} " .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}"
        );
    }

    public function removeOldBooking(string $orderId): void
    {
        /** @var Booking[] $bookings */
        $bookings = $this->bookingRepository->findBy(['OrderId' => $orderId]);
        foreach ($bookings as $booking) {
            $this->bookingRepository->delete($booking->getId());
        }
    }
}
