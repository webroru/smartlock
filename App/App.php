<?php

namespace App;

use App\Entity\Booking;
use App\Repository\RepositoryInterface;

class App
{
    private const REGISTRATION_DELAY = 60 * 60 * 24 * 7; // 1 week

    private $mailChecker;
    private $mailSender;
    private $scienerApi;
    private $bookingRepository;

    public function __construct(
        MailChecker $mailChecker,
        MailSender $mailSender,
        ScienerApi $scienerApi,
        RepositoryInterface $bookingRepository
    ) {
        $this->mailChecker = $mailChecker;
        $this->mailSender = $mailSender;
        $this->scienerApi = $scienerApi;
        $this->bookingRepository = $bookingRepository;
    }

    public function runReservationChecker(): void
    {
        foreach ($this->mailChecker->getMail() as $uid => $mail) {
            try {
                $this->processMail($mail);
            } catch (\Exception $e) {
                Logger::error($e->getMessage());
            }
            $this->mailChecker->setSeen($uid);
        }
    }

    public function runExpiredPasscodesRemover(): void
    {
        $this->scienerApi->removeExpiredPasscodes();
        Logger::log('Expired passcodes removed successfully');
    }

    public function checkDelayedBooking(): void
    {
        $checkInDate = new \DateTime(time() + self::REGISTRATION_DELAY);
        $bookings = $this->bookingRepository->getUnregisteredBookingsByDateRange($checkInDate);
        foreach ($bookings as $booking) {
            try {
                $this->registerBooking($booking, false);
            } catch (\Exception $e) {
                Logger::error($e->getMessage());
            }
        }
    }

    private function processMail(string $mail): void
    {
        $parser = new Parser($mail);
        $checkInDate = $parser->getCheckInDate();
        $checkOutDate = $parser->getCheckOutDate();
        $guestName = $parser->getGuestName();
        $email = $parser->getEmail();
        $orderId = $parser->getOrderId();
        $isChanged = $parser->isChanged();
        $email = $email !== '' ? $email : getenv('SUPPORT_EMAIL');
        $booking = (new Booking())
            ->setName($guestName)
            ->setEmail($email)
            ->setCheckInDate($this->prepareDate($checkInDate))
            ->setCheckOutDate($this->prepareDate($checkOutDate))
            ->setOrderId($orderId);

        if (strtotime($checkInDate) - time() <= self::REGISTRATION_DELAY) {
            $this->registerBooking($booking, $isChanged);
            return;
        }
        if ($isChanged) {
            $this->removeOldBooking($orderId);
        }
        $this->bookingRepository->add($booking);
    }

    private function prepareDate(string $date): \DateTime
    {
        return new \DateTime($date, new \DateTimeZone('Europe/Vienna'));
    }

    private function sendMail(Booking $booking, bool $isChanged): void
    {
        $body = "Dear {$booking->getName()}\n" .
            'You have ' . ($isChanged ? 'changes ' : 'a ') .
            "reservation at the Hotel \"GreenSLO\" from {$booking->getCheckInDate()->format('Y-m-d H:i')}" .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}\n" .
            "Your CODE from the MAIN DOOR of the HOTEL:  #{$booking->getCode()}#\n" .
            "This CODE will be VALID from the time of check-in and until check-out\n" .
            "(14:00 - check in, 12:00 - check out)\n" .
            "I ask you to SAVE this CODE to enter the hotel.\n" .
            "Best regards, Sergey";

        $this->mailSender->send($booking->getEmail(), $booking->getName(), 'Hotel GreenSLO Ljubljana', $body);
    }

    private function registerBooking(Booking $booking, bool $isChanged): void
    {
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

        $this->sendMail($booking, $isChanged);
        Logger::log(
            "For {$booking->getName()} have been added password: {$booking->getCode()} valid from " .
            "{$booking->getCheckInDate()->format('Y-m-d H:i')} " .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}"
        );
    }

    private function removeOldBooking(string $orderId): void
    {
        /** @var Booking[] $bookings */
        $bookings = $this->bookingRepository->findBy(['OrderId' => $orderId]);
        foreach ($bookings as $booking) {
            $this->bookingRepository->delete($booking->getId());
        }
    }
}
