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

    public function makeGetRequest(string $path, array $headers): ResponseInterface
    {
        return $this->makeRequest('GET', $path, $headers, '');
    }

    public function makePostRequest(string $path, array $headers, string $body): ResponseInterface
    {
        return $this->makeRequest('POST', $path, $headers, $body);
    }

    public function makePutRequest(string $path, array $headers, string $body): ResponseInterface
    {
        return $this->makeRequest('PUT', $path, $headers, $body);
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

    private function makeRequest(string $method, string $path, array $headers, string $body): ResponseInterface
    {
        return $this->client->request($method, $this->getUrl($path), [
            'headers' => [...$headers, ...$this->getHeaders()],
            'body' => $body,
            'max_redirects' => 0,
        ]);
    }
}
