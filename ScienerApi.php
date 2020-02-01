<?php

namespace App;

use GuzzleHttp\Client;

class ScienerApi
{
    const BASE_URL = 'https://api.sciener.cn';
    const APP_ID = '7af8fb46051e4f54984a94c6a5dcd46f';
    const APP_SECRET = 'e53cd30a6bcbcef49166833e8737dc0b';
    const GRANT_TYPE = 'password';
    const LOCK_ID = '1297585';
    const USER = '+38630387021';
    const PASSWORD = 'Itkiss19';
    const REDIRECT_URI = 'test.com';
    const GATEWAY = 2;
    const KEYBOARD_PWD_VERSION = 4;
    const KEYBOARD_PWD_TYPE = ['period' => 3];
    const PASSCODE_ATTEMPTS = 10;
    const SAME_PASSCODE_EXISTS = -3007;

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
                'client_id' => self::APP_ID,
                'client_secret' => self::APP_SECRET,
                'grant_type' => self::GRANT_TYPE,
                'username' => self::USER,
                'password' => md5(self::PASSWORD),
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
                'clientId' => self::APP_ID,
                'accessToken' => $this->token,
                'lockId' => self::LOCK_ID,
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
            try {
                $this->deletePasscode($passCode['keyboardPwdId']);
            } catch (\Exception $e) {
                \addLog("{$e->getMessage()}");
            }
        }
    }

    public function generatePasscode(string $name, int $startDate, int $endDate): string
    {
        $name = implode(' ', array_slice(explode(' ', $name), 0, 2));
        $response = $this->client->post('v3/keyboardPwd/get', [
            'form_params' => [
                'clientId' => self::APP_ID,
                'accessToken' => $this->token,
                'lockId' => self::LOCK_ID,
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
                'clientId' => self::APP_ID,
                'accessToken' => $this->token,
                'lockId' => self::LOCK_ID,
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
                'clientId' => self::APP_ID,
                'accessToken' => $this->token,
                'lockId' => self::LOCK_ID,
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
