<?php

declare(strict_types=1);

namespace App\Queue\Handlers;

use App\Queue\Job\JobInterface;

interface HandlerInterface
{
    public function handle(JobInterface $job);
}
