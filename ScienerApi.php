<?php

namespace App;

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

    private $client;
    private $token;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => self::BASE_URL]);
        $this->token = $this->getAccessToken();
    }

    public function getAccessToken()
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

    public function addPasscode(string $name, string $password, int $startDate, int $endDate)
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
            return null;
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    public function removeExpiredPasscodes()
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
}
