<?php

declare(strict_types=1);

namespace tests\App\Unit\Queue;

use App\Queue\HandlerInterface;
use App\Queue\Job\JobInterface;

class FakeHandler implements HandlerInterface
{
    public function handle(JobInterface $job): void
    {
    }
}
