<?php

namespace PonchoPay;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function PonchoPay\Utils\joinPaths;
use function PonchoPay\Utils\telemetry;

class Api
{
    private HttpClientInterface $client;
    private string $base;

    public function __construct(string $base = 'https://pay.ponchopay.com/')
    {
        $this->client = HttpClient::create();
        $this->base = $base;
    }

    private function getUrl(string $path): string
    {
        return joinPaths($this->base, $path);
    }

    private function getHeaders(): array
    {
        return [
          'content-type' => 'application/json',
          'x-telemetry' => json_encode(telemetry()),
        ];
    }

    public function makePostRequest(string $path, array $headers, string $body): ResponseInterface
    {
        return $this->client->request('POST', $this->getUrl($path), [
            'headers' => [...$headers, ...$this->getHeaders()],
            'body' => $body,
            'max_redirects' => 0
        ]);
    }


    public function makePutRequest(string $path, array $headers, string $body): ResponseInterface
    {
        return $this->client->request('PUT', $this->getUrl($path), [
            'headers' => [...$headers, ...$this->getHeaders()],
            'body' => $body,
            'max_redirects' => 0
        ]);
    }
}
