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

    public function __construct(ClientInterface $client, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    public function setPropKey(string $propKey): void
    {
        $this->propKey = $propKey;
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
        if (isset($message['error'])) {
            throw new \Exception($message['error']);
        }
    }

    public function getInvoices(array $requestData): array
    {
        $requestData['authentication'] = $this->getAuth();

        $response = $this->client->request(
            'POST',
            self::HOST . '/' . self::DATA_TYPE . '/getInvoices',
            [
                RequestOptions::JSON => $requestData,
                'http_errors' => false,
            ]
        );

        $content = $response->getBody()->getContents();
        $message = json_decode($content, true);
        if (isset($message['error'])) {
            throw new \Exception($message['error']);
        }

        return $message;
    }

    private function getAuth(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'propKey' => $this->propKey,
        ];
    }
}
