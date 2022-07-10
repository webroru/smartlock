<?php

declare(strict_types=1);

namespace tests\App\Unit;

use App\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testCritical()
    {
        Logger::critical('test');
    }
}
