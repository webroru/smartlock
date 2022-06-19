<?php

declare(strict_types=1);

namespace tests\App\Unit\Providers\Beds24\Sciener\Client;

use App\Providers\Sciener\Client\Client;
use tests\App\Unit\UnitTestCase;

class ClientTest extends UnitTestCase
{
    private ?Client $client;

    protected function setUp(): void
    {
        $this->client = $this->getContainer()->get(Client::class);
    }

    public function testGeneratePasscode()
    {
        $passcode = $this->client->generatePasscode('Test', time() * 1000, (time() + 60 * 60 * 24) * 1000);
        $this->assertIsString($passcode);
    }

    public function testGetAllPasscodes()
    {
        $passcodes = $this->client->getAllPasscodes();
        $this->assertIsArray($passcodes);
    }

    public function testAddPasscode()
    {
        $result = $this->client->addPasscode('Test', '0101', time() * 1000, (time() + 60 * 60 * 24) * 1000);
        $this->assertIsInt($result);
    }
}
