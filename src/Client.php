<?php

namespace PonchoPay;

use PonchoPay\PonchoPayException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function PonchoPay\createToken;

final class Client
{
    private static string $INITIATE_PAYMENT_ENDPOINT = '/api/integration/generic/initiate';

    private string $key;
    private string $base;
    private HttpClientInterface $client;

    public function __construct(string $key, string $base = 'https://pay.ponchopay.com/')
    {
        $this->key = $key;
        $this->base = trim($base, '/');
        $this->client = HttpClient::create();
    }

    private function getUrl(string $endpoint): string
    {
        return "{$this->base}{$endpoint}";
    }

    /**
     * @param array{metadata: string, urn: string, amount: int, email: string, note?: string, expiry?: \DateTimeInterface|string} $init
     */
    private function getData(array $init): string
    {
        $optionals = [];
        if (array_key_exists('expiry', $init)) {
            if ($init['expiry'] instanceof \DateTimeInterface) {
                $optionals['expiry'] = $init['expiry']->format(\DateTimeInterface::ATOM);
            } else {
                $optionals['expiry'] = $init['expiry'];
            }
        }

        return json_encode([...$init, ...$optionals, 'token' => createToken($this->key, $init['metadata'])]);
    }

    /**
     * @param array{metadata: string, urn: string, amount: int, email: string, note?: string} $init
     */
    public function initiatePayment(array $init): string
    {
        $response = $this->client->request('POST', $this->getUrl(Client::$INITIATE_PAYMENT_ENDPOINT), [
            'headers' => [ 'content-type' => 'application/json' ],
            'body' => $this->getData($init),
            'max_redirects' => 0
        ]);

        if($response->getStatusCode() === 302) {
            return $response->getHeaders(false)['location'][0];
        }

        throw new PonchoPayException("Unexpected response. Expected 302 as status code but {$response->getStatusCode()} was received");
    }
}
