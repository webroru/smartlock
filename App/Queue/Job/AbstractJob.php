<?php

declare(strict_types=1);

namespace App\Queue\Job;

abstract class AbstractJob implements JobInterface
{
    public int $attempts = 10;
}
