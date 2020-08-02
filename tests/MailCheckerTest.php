<?php

namespace App\tests;

use App\MailChecker;
use PHPUnit\Framework\TestCase;

class MailCheckerTest extends TestCase
{
    /** @var MailChecker */
    private $mailChecker;

    protected function setUp(): void
    {
        // Load .env
        \Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();
        $this->mailChecker = new MailChecker();
    }

    public function test__construct(): void
    {
        $this->assertInstanceOf(
            MailChecker::class,
            ($this->mailChecker)
        );
    }

    public function testGetMail()
    {
        $this->mailChecker->getMail();
        $this->assertIsArray($this->mailChecker->getMail());
    }
}
