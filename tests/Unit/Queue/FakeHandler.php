<?php

declare(strict_types=1);

namespace tests\App\Unit\Queue;

use App\Queue\Handlers\HandlerInterface;
use App\Queue\Job\JobInterface;

class FakeHandler implements HandlerInterface
{
    public function handle(JobInterface $job): void
    {
    }
}
