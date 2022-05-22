<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Booking;
use App\Logger;
use App\Providers\Sciener\Client\Client;

class LockService
{
    private const IGNORE_PASSCODES = [37782310, 37780116, 37663144, 37663134, 9318334];
    private const PASSCODE_ATTEMPTS = 10;

    private Client $scienerApi;

    public function __construct(
        Client $scienerApi
    ) {
        $this->scienerApi = $scienerApi;
    }

    public function removeExpiredPasscodes(): void
    {
        $passCodes = array_filter($this->scienerApi->getAllPasscodes(), function (array $item) {
            return $item['endDate'] !== 0 && $item['endDate'] < time() * 1000;
        });

        foreach ($passCodes as $passCode) {
            if (in_array($passCode['keyboardPwdId'], self::IGNORE_PASSCODES)) {
                continue;
            }
            try {
                $this->scienerApi->deletePasscode($passCode['keyboardPwdId']);
            } catch (\Exception $e) {
                Logger::error("{$e->getMessage()}");
            }
        }
    }

    public function addRandomPasscode(Booking $booking): string
    {
        $name = $booking->getName();
        $startDate = $booking->getCheckInDate()->getTimestamp() * 1000;
        $endDate = $booking->getCheckOutDate()->getTimestamp() * 1000;

        for ($i = 0; $i < self::PASSCODE_ATTEMPTS; $i++) {
            $password = sprintf('%04d', mt_rand(0, 9999));
            if ($this->scienerApi->addPasscode($name, $password, $startDate, $endDate)) {
                return $password;
            }
        }

        throw new \Exception('All attempts are spent.');
    }
}
