<?php

declare(strict_types=1);

namespace App\Queue;

interface JobInterface
{
    public function execute(): void;
}
