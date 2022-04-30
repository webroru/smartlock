<?php

namespace App\Services;

use App\Logger;
use GuzzleHttp\Client;

class ScienerApi
{
    private const BASE_URL = 'https://api.sciener.cn';
    private const GRANT_TYPE = 'password';
    private const REDIRECT_URI = 'test.com';
    private const GATEWAY = 2;
    private const KEYBOARD_PWD_VERSION = 4;
    private const KEYBOARD_PWD_TYPE = ['period' => 3];
    private const PASSCODE_ATTEMPTS = 10;
    private const SAME_PASSCODE_EXISTS = -3007;
    private const IGNORE_PASSCODES = [37782310, 37780116, 37663144, 37663134, 9318334];

    private $client;
    private $token;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => self::BASE_URL]);
        $this->token = $this->getAccessToken();
    }

    public function getAccessToken(): ?string
    {
        $response = $this->client->post('oauth2/token', [
            'form_params' => [
                'client_id' => getenv('SCIENER_APP_ID'),
                'client_secret' => getenv('SCIENER_APP_SECRET'),
                'grant_type' => self::GRANT_TYPE,
                'username' => getenv('SCIENER_USER'),
                'password' => md5(getenv('SCIENER_PASSWORD')),
                'redirect_uri' => self::REDIRECT_URI,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return json_decode($response->getBody())->access_token ?? null;
    }

    public function addPasscode(string $name, string $password, int $startDate, int $endDate): bool
    {
        $response = $this->client->post('v3/keyboardPwd/add', [
            'form_params' => [
                'clientId' => getenv('SCIENER_APP_ID'),
                'accessToken' => $this->token,
                'lockId' => getenv('SCIENER_LOCK_ID'),
                'keyboardPwd' => $password,
                'keyboardPwdName' => $name,
                'startDate' => $startDate,
                'addType' => self::GATEWAY,
                'endDate' => $endDate,
                'date' => time() * 1000,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Cant't generate passcode for $name");
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['errcode'])) {
            if ($result['errcode'] === self::SAME_PASSCODE_EXISTS) {
                return false;
            }
            throw new \Exception("Error during passcode generation for $name: {$result['errmsg']}");
        }
        return isset($result['keyboardPwdId']);
    }

    public function removeExpiredPasscodes(): void
    {
        $passCodes = array_filter($this->getAllPasscodes(), function (array $item) {
            return $item['endDate'] !== 0 && $item['endDate'] < time() * 1000;
        });

        foreach ($passCodes as $passCode) {
            if (in_array($passCode['keyboardPwdId'], self::IGNORE_PASSCODES)) {
                continue;
            }
            try {
                $this->deletePasscode($passCode['keyboardPwdId']);
            } catch (\Exception $e) {
                Logger::error("{$e->getMessage()}");
            }
        }
    }

    public function generatePasscode(string $name, int $startDate, int $endDate): string
    {
        $name = implode(' ', array_slice(explode(' ', $name), 0, 2));
        $response = $this->client->post('v3/keyboardPwd/get', [
            'form_params' => [
                'clientId' => getenv('SCIENER_APP_ID'),
                'accessToken' => $this->token,
                'lockId' => getenv('SCIENER_LOCK_ID'),
                'keyboardPwdVersion' => self::KEYBOARD_PWD_VERSION,
                'keyboardPwdType' => self::KEYBOARD_PWD_TYPE['period'],
                'keyboardPwdName' => $name,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'date' => time() * 1000,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Cant't generate passcode for $name");
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['errcode'])) {
            throw new \Exception("Error during passcode generation for $name: {$result['errmsg']} {$result['description']}");
        }
        return $result['keyboardPwd'];
    }

    public function addRandomPasscode(string $name, int $startDate, int $endDate): ?string
    {
        for ($i = 0; $i < self::PASSCODE_ATTEMPTS; $i++) {
            $password = sprintf('%04d', mt_rand(0, 9999));
            if ($this->addPasscode($name, $password, $startDate, $endDate)) {
                return $password;
            }
        }
        return null;
    }

    private function getAllPasscodes(int $pageNo = 1)
    {
        $response = $this->client->post('v3/lock/listKeyboardPwd ', [
            'form_params' => [
                'clientId' => getenv('SCIENER_APP_ID'),
                'accessToken' => $this->token,
                'lockId' => getenv('SCIENER_LOCK_ID'),
                'pageNo' => $pageNo,
                'pageSize' => 100,
                'date' => time() * 1000,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Cant\'t get list of passcodes.');
        }

        $result = json_decode($response->getBody()->getContents(), true);
        $list = $result['list'];
        if ($pageNo !== $result['pages']) {
            $list = array_merge($list, $this->getAllPasscodes(++$pageNo));
        }

        return $list;
    }

    private function deletePasscode(int $keyboardPwdId): void
    {
        $response = $this->client->post('v3/keyboardPwd/delete ', [
            'form_params' => [
                'clientId' => getenv('SCIENER_APP_ID'),
                'accessToken' => $this->token,
                'lockId' => getenv('SCIENER_LOCK_ID'),
                'keyboardPwdId' => $keyboardPwdId,
                'deleteType' => self::GATEWAY,
                'date' => time() * 1000,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Cant\'t delete passcode with id: $keyboardPwdId");
        }

        $result = json_decode($response->getBody()->getContents(), true);
        if ($result['errcode']) {
            throw new \Exception("Error during removing passcode with id $keyboardPwdId: {$result['errmsg']}");
        }
    }
}
