<?php

namespace App\Commands;

use App\Logger;
use App\ScienerApi;

class RemoveExpiredPasscodes
{
    private $scienerApi;

    public function __construct(
        ScienerApi $scienerApi
    ) {
        $this->scienerApi = $scienerApi;
    }

    public function execute(): void
    {
        $this->scienerApi->removeExpiredPasscodes();
        Logger::log('Expired passcodes removed successfully');
    }
}