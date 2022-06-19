<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helpers\PhoneHepler;
use App\Logger;
use App\Queue\Job\GetPasscode;
use App\Queue\RabbitMQ\Dispatcher;
use App\Repository\BookingRepositoryInterface;
use App\Services\BookingService;
use App\Services\LockService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly LockService $lockService,
        private readonly Dispatcher $dispatcher,
        private readonly string $token
    ) {
    }

    public function create(Request $request): Response
    {
        $authorizationHeader = $request->headers->get('authorization', '');
        $token = explode(' ', $authorizationHeader)[1] ?? '';
        if (!$this->validateToken($token)) {
            Logger::log("Authorization is not valid: Token is $token");
            return new Response(
                'Authorization is not valid',
                Response::HTTP_UNAUTHORIZED,
                ['content-type' => 'text/html']
            );
        }

        $data = $request->toArray();

        try {
            $booking = $this->bookingService->create($data);
            $bookingId = $this->bookingRepository->add($booking);
            $this->dispatcher->add(new GetPasscode($bookingId));
            Logger::log("New GetPasscode Job added For {$booking->getName()} reservation");
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return new Response(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }
        return new Response(
            'The Booking has been processed',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    public function remove(Request $request): Response
    {
        $authorizationHeader = $request->headers->get('authorization', '');
        $token = explode(' ', $authorizationHeader)[1] ?? '';
        if (!$this->validateToken($token)) {
            Logger::log("Authorization is not valid: Token is $token");
            return new Response(
                'Authorization is not valid',
                Response::HTTP_UNAUTHORIZED,
                ['content-type' => 'text/html']
            );
        }

        $data = $request->toArray();

        try {
            $booking = $this->bookingService->create($data);
        } catch (\Exception $e) {
            Logger::log($e->getMessage());
            return new Response(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }

        try {
            $this->lockService->removePasscode($booking);
        } catch (\Exception $e) {
            $error = "Couldn't remove passcode. Error: {$e->getMessage()}. " .
                "Guest: {$booking->getName()}, " .
                "Reservation: {$booking->getCheckInDate()->format('Y-m-d H:i')} — " .
                "{$booking->getCheckOutDate()->format('Y-m-d H:i')}, " .
                "Order №: {$booking->getOrderId()}.";

            Logger::log($error);
            return new Response(
                $error,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }

        return new Response(
            'The Booking has been processed',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    public function clearPhoneNumber(Request $request): Response
    {
        $authorizationHeader = $request->headers->get('authorization', '');
        $token = explode(' ', $authorizationHeader)[1] ?? '';
        if (!$this->validateToken($token)) {
            Logger::log("Authorization is not valid: Token is $token");
            return new Response(
                'Authorization is not valid',
                Response::HTTP_UNAUTHORIZED,
                ['content-type' => 'text/html']
            );
        }

        $data = $request->toArray();

        try {
            $booking = $this->bookingService->create($data);
        } catch (\Exception $e) {
            Logger::log($e->getMessage());
            return new Response(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }

        $phone = PhoneHepler::clear($data['phone']);
        $booking->setPhone($phone);

        try {
            $this->bookingService->updatePhone($booking);
        } catch (\Exception $e) {
            Logger::log($e->getMessage());
            return new Response(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }

        return new Response(
            'Phone number has been cleared',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    private function validateToken(string $token): bool
    {
        return $token === $this->token;
    }
}
