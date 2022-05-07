<?php

declare(strict_types=1);

namespace App\Providers\Beds24\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class ClientV1 implements Beds24ClientInterface
{
    private const HOST = 'https://api.beds24.com';
    private const DATA_TYPE = 'json';

    private string $apiKey;
    private string $propKey;
    private ClientInterface $client;

    public function __construct(ClientInterface $client, string $apiKey, string $propKey)
    {
        $this->apiKey = $apiKey;
        $this->propKey = $propKey;
        $this->client = $client;
    }

    public function setBooking(array $requestData): void
    {
        $requestData['authentication'] = $this->getAuth();

        $response = $this->client->request(
            'POST',
            self::HOST . '/' . self::DATA_TYPE . '/setBooking',
            [
                RequestOptions::JSON => $requestData,
                'http_errors' => false,
            ]
        );

        $content = $response->getBody()->getContents();
        $message = json_decode($content, true);
        if ($message['error']) {
            throw new \Exception($message['error']);
        }
    }

    private function getAuth(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'propKey' => $this->propKey,
        ];
    }
}
