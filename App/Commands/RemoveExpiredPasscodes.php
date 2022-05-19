<?php

namespace App\Commands;

use App\Logger;
use App\Providers\Sciener\Client\Client;

class RemoveExpiredPasscodes
{
    private $scienerApi;

    public function __construct(
        Client $scienerApi
    ) {
        $this->scienerApi = $scienerApi;
    }

    public function execute(): void
    {
        $this->scienerApi->removeExpiredPasscodes();
        Logger::log('Expired passcodes removed successfully');
    }
}