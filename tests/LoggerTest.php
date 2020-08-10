<?php

namespace App;

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testLog(): void
    {
        Logger::log('test');
        $file = __DIR__ . '/../logs/app.log';
        $data = file($file);
        $line = $data[count($data) - 1];
        $this->assertStringContainsString('test', $line);
    }

    public function testError(): void
    {
        Logger::error('test', false);
        $file = __DIR__ . '/../logs/error.log';
        $data = file($file);
        $line = $data[count($data) - 1];
        $this->assertStringContainsString('test', $line);
    }
}
