<?php

namespace App;

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
        BookingRepository $bookingRepository
    ) {
        $this->mailChecker = $mailChecker;
        $this->mailSender = $mailSender;
        $this->scienerApi = $scienerApi;
        $this->bookingRepository = $bookingRepository;
    }

    public function runReservationChecker(): void
    {
        foreach ($this->mailChecker->getMail() as $uid => $mail) {
            $this->processMail($mail);
            $this->mailChecker->setSeen($uid);
        }
    }

    public function runExpiredPasscodesRemover(): void
    {
        $this->scienerApi->removeExpiredPasscodes();
        Logger::log('Expired passcodes removed successfully');
    }

    private function processMail(string $mail): void
    {
        $parser = new Parser($mail);
        $checkInDate = $parser->getCheckInDate();
        $checkOutDate = $parser->getCheckOutDate();
        $guestName = $parser->getGuestName();
        $email = $parser->getEmail();
        $isChanged = $parser->isChanged();
        $email = $email !== '' ? $email : getenv('SUPPORT_EMAIL');
        $booking = (new Booking())
            ->setName($guestName)
            ->setEmail($email)
            ->setCheckInDate($this->prepareCheckInDate($checkInDate)) // использовать prepareCheckInDate
            ->setCheckOutDate($this->prepareCheckOutDate($checkOutDate));

        if (time() - strtotime($checkInDate) <= self::REGISTRATION_DELAY) {
            $this->registerBooking($booking, $isChanged);
        } else {
            $this->bookingRepository->add($booking);
        }
    }

    private function prepareCheckInDate(string $date): \DateTime
    {
        return new \DateTime("$date 14:00", new \DateTimeZone('Europe/Vienna'));
    }

    private function prepareCheckOutDate(string $date): \DateTime
    {
        return new \DateTime("$date 12:00", new \DateTimeZone('Europe/Vienna'));
    }

    private function sendMail(Booking $booking, string $password, bool $isChanged): void
    {
        $body = "Dear {$booking->getName()}\n" .
            'You have ' . ($isChanged ? 'changes ' : 'a ') .
            "reservation at the Hotel \"GreenSLO\" from {$booking->getCheckInDate()->format('Y-m-d H:i')}" .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}\n" .
            "Your CODE from the MAIN DOOR of the HOTEL:  #$password#\n" .
            "This CODE will be VALID from the time of check-in and until check-out\n" .
            "(14:00 - check in, 12:00 - check out)\n" .
            "I ask you to SAVE this CODE to enter the hotel.\n" .
            "Best regards, Sergey";

        $this->mailSender->send($booking->getEmail(), $booking->getName(), 'Hotel GreenSLO Ljubljana', $body);
    }

    private function registerBooking(Booking $booking, bool $isChanged): void
    {
        $password = $this->scienerApi->generatePasscode(
            $booking->getName(),
            $booking->getCheckInDate()->getTimestamp() * 1000,
            $booking->getCheckOutDate()->getTimestamp() * 1000
        );
        $this->sendMail($booking, $password, $isChanged);
        Logger::log(
            "For {$booking->getName()} have been added password: $password valid from " .
            "{$booking->getCheckInDate()->format('Y-m-d H:i')} " .
            "to {$booking->getCheckOutDate()->format('Y-m-d H:i')}"
        );
    }
}
