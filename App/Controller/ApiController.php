<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Booking;
use App\Services\LockService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    private LockService $lockService;

    public function __construct(LockService $lockService)
    {
        $this->lockService = $lockService;
    }

    public function create(Request $request): Response
    {
        $checkInDate = $request->get('checkindate');
        $checkOutDate = $request->get('checkoutdate');
        $guestName = $request->get('guestname');
        $email = $request->get('email');
        $orderId = $request->get('orderid');

        $message = 'The Booking has been processed';
        $status = Response::HTTP_OK;

        if (!$checkInDate || !$checkOutDate || !$guestName || !$email || !$orderId) {
            $message = 'Validation error';
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;

            return new Response(
                $message,
                $status,
                ['content-type' => 'text/html']
            );
        }

        $booking = (new Booking())
            ->setName($guestName)
            ->setEmail($email)
            ->setCheckInDate($this->prepareDate($checkInDate))
            ->setCheckOutDate($this->prepareDate($checkOutDate))
            ->setOrderId($orderId);

        $this->lockService->registerBooking($booking);

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
