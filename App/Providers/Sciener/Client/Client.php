<?php

declare(strict_types=1);

namespace App\Providers\Sciener\Client;

use GuzzleHttp\ClientInterface;

class Client
{
    private const BASE_URL = 'https://euapi.sciener.com';
    private const GRANT_TYPE = 'password';
    private const REDIRECT_URI = 'test.com';
    private const GATEWAY = 2;
    private const KEYBOARD_PWD_VERSION = 4;
    private const KEYBOARD_PWD_TYPE = ['period' => 3];
    private const SAME_PASSCODE_EXISTS = -3007;

    private ClientInterface $client;
    private string $token;
    private string $appId;

    public function __construct(
        ClientInterface $client,
        string $appId,
        string $appSecret,
        string $user,
        string $password
    ) {
        $this->client = $client;
        $this->appId = $appId;

        $this->token = $this->getAccessToken($appSecret, $user, $password);
    }

    public function addPasscode(string $name, string $password, int $startDate, int $endDate, string $lockId): int
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/add',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $lockId,
                    'keyboardPwd' => $password,
                    'keyboardPwdName' => $name,
                    'startDate' => $startDate,
                    'addType' => self::GATEWAY,
                    'endDate' => $endDate,
                    'date' => time() * 1000,
                ],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Can't generate passcode for $name");
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['errcode'])) {
            if ($result['errcode'] === self::SAME_PASSCODE_EXISTS) {
                throw new \Exception("Passcode $password already exists");
            }
            throw new \Exception("Error during passcode generation for $name: {$result['errmsg']}");
        }
        return $result['keyboardPwdId'];
    }

    public function changePasscode(string $name, int $passwordId, int $startDate, int $endDate, string $lockId): void
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/change',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $lockId,
                    'keyboardPwdId' => $passwordId,
                    'keyboardPwdName' => $name,
                    'startDate' => $startDate,
                    'changeType' => self::GATEWAY,
                    'endDate' => $endDate,
                    'date' => time() * 1000,
                ],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Can't generate passcode for $name");
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['errcode'])) {
            if ($result['errcode'] === self::SAME_PASSCODE_EXISTS) {
                throw new \Exception("Passcode already exists");
            }
            throw new \Exception("Error during passcode generation for $name: {$result['errmsg']}");
        }
    }

    public function generatePasscode(string $name, int $startDate, int $endDate, string $lockId): string
    {
        $name = implode(' ', array_slice(explode(' ', $name), 0, 2));
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/get',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $lockId,
                    'keyboardPwdVersion' => self::KEYBOARD_PWD_VERSION,
                    'keyboardPwdType' => self::KEYBOARD_PWD_TYPE['period'],
                    'keyboardPwdName' => $name,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'date' => time() * 1000,
                ],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Can't generate passcode for $name");
        }
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['errcode'])) {
            throw new \Exception("Error during passcode generation for $name: {$result['errmsg']} {$result['description']}");
        }
        return $result['keyboardPwd'];
    }

    public function getAllPasscodes(string $lockId, int $pageNo = 1): array
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/lock/listKeyboardPwd ',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $lockId,
                    'pageNo' => $pageNo,
                    'pageSize' => 100,
                    'date' => time() * 1000,
                ],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Cant\'t get list of passcodes.');
        }

        $result = json_decode($response->getBody()->getContents(), true);
        $list = $result['list'];
        if ($pageNo !== $result['pages']) {
            $list = array_merge($list, $this->getAllPasscodes($lockId, ++$pageNo));
        }

        return $list;
    }

    public function deletePasscode(int $keyboardPwdId, string $lockId): void
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/delete ',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $lockId,
                    'keyboardPwdId' => $keyboardPwdId,
                    'deleteType' => self::GATEWAY,
                    'date' => time() * 1000,
                ],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Cant\'t delete passcode with id: $keyboardPwdId");
        }

        $result = json_decode($response->getBody()->getContents(), true);
        if ($result['errcode']) {
            throw new \Exception("Error during removing passcode with id $keyboardPwdId: {$result['errmsg']}");
        }
    }

    private function getAccessToken(string $clientSecret, string $username, string $password): ?string
    {
        $response = $this->client->post(
            self::BASE_URL . '/oauth2/token',
            [
                'form_params' => [
                    'client_id' => $this->appId,
                    'client_secret' => $clientSecret,
                    'grant_type' => self::GRANT_TYPE,
                    'username' => $username,
                    'password' => md5($password),
                    'redirect_uri' => self::REDIRECT_URI,
                ],
            ],
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Can not get access token');
        }

        return json_decode($response->getBody()->getContents())->access_token ?? null;
    }
}
