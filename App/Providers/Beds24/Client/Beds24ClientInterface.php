<?php

declare(strict_types=1);

namespace App\Providers\Beds24\Client;

interface Beds24ClientInterface
{
    public function setBooking(array $requestData): void;
    public function getInvoices(array $requestData): array;
}
