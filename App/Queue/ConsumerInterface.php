<?php

declare(strict_types=1);

namespace App\Queue;

interface ConsumerInterface
{
    public function work(): void;
}
