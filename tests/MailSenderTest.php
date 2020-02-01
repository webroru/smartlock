<?php

namespace App\tests;

use App\MailSender;
use PHPUnit\Framework\TestCase;

class MailSenderTest extends TestCase
{
    public function test__construct(): void
    {
        $this->assertInstanceOf(
            MailSender::class,
            (new MailSender())
        );
    }
}
