<?php

declare(strict_types=1);

namespace tests\App\Unit\Helpers;

use App\Helpers\PhoneHepler;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function testClear()
    {
        $this->assertEquals('79876543210', PhoneHepler::clear('+7 (987) 654-32-10'));
    }
}
