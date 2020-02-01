<?php

namespace App\tests;

use App\MailChecker;
use PHPUnit\Framework\TestCase;

class MailCheckerTest extends TestCase
{

    public function test__construct(): void
    {
        $this->assertInstanceOf(
            MailChecker::class,
            (new MailChecker())
        );
    }
}
