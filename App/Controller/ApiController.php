<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Booking;
use App\Services\BookingService;
use App\Services\LockService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    private LockService $lockService;
    private BookingService $bookingService;

    public function __construct(LockService $lockService, BookingService $bookingService)
    {
        $this->lockService = $lockService;
        $this->bookingService = $bookingService;
    }

    public function create(Request $request): Response
    {
        $data = $request->toArray();
        $checkInDate = $data['checkindate'] ?? null;
        $checkOutDate = $data['checkoutdate'] ?? null;
        $guestName = $data['guestname'] ?? null;
        $orderId = $data['orderid'] ?? null;

        $message = 'The Booking has been processed';
        $status = Response::HTTP_OK;

        if (!$checkInDate || !$checkOutDate || !$guestName || !$orderId) {
            return new Response(
                'Validation error',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['content-type' => 'text/html']
            );
        }

        $booking = (new Booking())
            ->setName($guestName)
            ->setCheckInDate($this->prepareDate($checkInDate))
            ->setCheckOutDate($this->prepareDate($checkOutDate))
            ->setOrderId($orderId);

        try {
            $password = $this->lockService->getPassword($booking);
            $booking->setCode($password);
            $this->bookingService->updateCode($booking);
        } catch (\Exception $e) {
            $error = "Couldn't register new passcode for the booking. Error: {$e->getMessage()}." .
                "Guest: {$booking->getName()}, " .
                "Reservation: {$booking->getCheckInDate()->format('Y-m-d H:i')} — " .
                "{$booking->getCheckOutDate()->format('Y-m-d H:i')}, " .
                "Order №: {$booking->getOrderId()}.";

            return new Response(
                $error,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }

        return new Response(
            $message,
            $status,
            ['content-type' => 'text/html']
        );
    }

    private function prepareDate(string $date): \DateTime
    {
        return new \DateTime($date, new \DateTimeZone('Europe/Vienna'));
    }
}
