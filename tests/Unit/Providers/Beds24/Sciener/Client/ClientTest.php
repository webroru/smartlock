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
        $passcode = $this->client->generatePasscode('Test', time() * 1000, (time() + 60 * 60 * 24) * 1000, '6525677');
        $this->assertIsString($passcode);
    }

    public function testGetAllPasscodes()
    {
        $passcodes = $this->client->getAllPasscodes('6525677');
        $this->assertIsArray($passcodes);
    }

    public function testAddPasscode()
    {
        $start = time() * 1000;
        $end = (time() + 60 * 60 * 24) * 1000;
        $name = 'Test';
        $password = sprintf('%06d', rand(0, 9999));
        $lockId = '6755296';
        $passwordId = $this->client->addPasscode($name, $password, $start, $end, $lockId);
        $this->client->deletePasscode($passwordId, $lockId);
        $this->assertIsInt($passwordId);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testChangePasscode()
    {
        $start = time() * 1000;
        $end = (time() + 60 * 60 * 24) * 1000;
        $name = 'Test';
        $password = sprintf('%06d', rand(0, 9999));
        $lockId = '6755296';
        $passwordId = $this->client->addPasscode($name, $password, $start, $end, $lockId);
        $this->client->changePasscode($name, $passwordId, $start, $end, $lockId);
        $this->client->deletePasscode($passwordId, $lockId);
    }
}
