<?php

declare(strict_types=1);

namespace App\Providers\Sciener\Client;

use App\Logger;
use GuzzleHttp\ClientInterface;

class Client
{
    private const BASE_URL = 'https://api.sciener.cn';
    private const GRANT_TYPE = 'password';
    private const REDIRECT_URI = 'test.com';
    private const GATEWAY = 2;
    private const KEYBOARD_PWD_VERSION = 4;
    private const KEYBOARD_PWD_TYPE = ['period' => 3];
    private const SAME_PASSCODE_EXISTS = -3007;

    private ClientInterface $client;
    private string $token;
    private string $appId;
    private string $lockId;

    public function __construct(
        ClientInterface $client,
        string $appId,
        string $lockId,
        string $appSecret,
        string $user,
        string $password
    ) {
        $this->client = $client;
        $this->appId = $appId;
        $this->lockId = $lockId;

        $this->token = $this->getAccessToken($appSecret, $user, $password);
    }

    public function addPasscode(string $name, string $password, int $startDate, int $endDate): bool
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/add',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $this->lockId,
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
                return false;
            }
            throw new \Exception("Error during passcode generation for $name: {$result['errmsg']}");
        }
        return isset($result['keyboardPwdId']);
    }

    public function generatePasscode(string $name, int $startDate, int $endDate): string
    {
        $name = implode(' ', array_slice(explode(' ', $name), 0, 2));
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/get',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $this->lockId,
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

    public function getAllPasscodes(int $pageNo = 1): array
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/lock/listKeyboardPwd ',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $this->lockId,
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
            $list = array_merge($list, $this->getAllPasscodes(++$pageNo));
        }

        return $list;
    }

    public function deletePasscode(int $keyboardPwdId): void
    {
        $response = $this->client->post(
            self::BASE_URL . '/v3/keyboardPwd/delete ',
            [
                'form_params' => [
                    'clientId' => $this->appId,
                    'accessToken' => $this->token,
                    'lockId' => $this->lockId,
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
