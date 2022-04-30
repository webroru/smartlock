<?php

namespace App\tests;

use App\Services\ScienerApi;
use PHPUnit\Framework\TestCase;

class ScienerApiTest extends TestCase
{
    /** @var ScienerApi */
    private $scienerApi;

    protected function setUp(): void
    {
        $this->scienerApi = new ScienerApi();
    }

    public function test__construct(): void
    {
        $this->assertInstanceOf(
            ScienerApi::class,
            ($this->scienerApi)
        );
    }

    public function testGetAccessToken(): void
    {
        $this->assertIsString($this->scienerApi->getAccessToken());
    }
}
