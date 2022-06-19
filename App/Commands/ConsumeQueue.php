<?php

declare(strict_types=1);

namespace App\Commands;

use App\Logger;
use App\Queue\RabbitMQ\Consumer;

class ConsumeQueue
{
    public function __construct(
        private readonly Consumer $consumer,
    ) {
    }

    public function execute(): void
    {
        Logger::log('Queue consumer is running');
        $this->consumer->work();
    }
}
