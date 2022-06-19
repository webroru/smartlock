<?php

declare(strict_types=1);

namespace App\Queue;

use App\Queue\Job\JobInterface;

interface DispatcherInterface
{
    public function add(JobInterface $job): void;
}
