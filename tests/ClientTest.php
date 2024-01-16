<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;
use Ponchopay\PonchoPayException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use PonchoPay\Client;

define('TOKEN', 'KxS2N+iLo3WCgBbPN8sAEUUaA2jNOLuVG/kkkqQrBI4=') ;
define('DEFAULTS', [
  'metadata' => 'order-1234',
  'urn' => 'URN1234',
  'amount' => 1234,
  'email' => 'some@guy.com',
]);

function getRedirectResponse(string $url): MockResponse
{
    return new MockResponse('', [
      'http_code' => 302,
      'response_headers' => ['Location' => $url],
    ]);
}

class ClientTest extends TestCase
{
    public function testInitiatePaymentRequestsUrlForPayment(): void
    {
        $url = 'http://some/url';
        $response = getRedirectResponse($url);
        $http = new MockHttpClient([$response]);

        $client = new Client('key');
        (fn () => $this->client = $http)->call($client);
        $this->assertSame($client->initiatePayment(DEFAULTS), $url);

        $this->assertSame($response->getRequestMethod(), 'POST');
        $this->assertSame($response->getRequestUrl(), 'https://pay.ponchopay.com/api/integration/generic/initiate');

        $options = $response->getRequestOptions();
        $this->assertContains('content-type: application/json', $options['headers']);
        $this->assertSame($options['body'], json_encode([...DEFAULTS, 'token' => TOKEN]));
    }

    public function testInitiatePaymentRequestsUrlForPaymentOnAnotherServer(): void
    {
        $url = 'http://some/url';
        $response = getRedirectResponse($url);
        $http = new MockHttpClient([$response]);

        $client = new Client('key', 'http://other.server/');
        (fn () => $this->client = $http)->call($client);
        $this->assertSame($client->initiatePayment(DEFAULTS), $url);

        $this->assertSame($response->getRequestMethod(), 'POST');
        $this->assertSame($response->getRequestUrl(), 'http://other.server/api/integration/generic/initiate');

        $options = $response->getRequestOptions();
        $this->assertContains('content-type: application/json', $options['headers']);
        $this->assertSame($options['body'], json_encode([...DEFAULTS, 'token' => TOKEN]));
    }

    public function testInitiatePaymentRequestsUrlForPaymentWithNote(): void
    {
        $url = 'http://some/url';
        $response = getRedirectResponse($url);
        $http = new MockHttpClient([$response]);
        $init = [...DEFAULTS, 'note' => "Here's your dough, now go have some fun-dough"];

        $client = new Client('key');
        (fn () => $this->client = $http)->call($client);
        $this->assertSame($client->initiatePayment($init), $url);

        $this->assertSame($response->getRequestMethod(), 'POST');
        $this->assertSame($response->getRequestUrl(), 'https://pay.ponchopay.com/api/integration/generic/initiate');

        $options = $response->getRequestOptions();
        $this->assertContains('content-type: application/json', $options['headers']);
        $this->assertSame($options['body'], json_encode([...$init, 'token' => TOKEN]));
    }

    public static function expiryDataProvider(): array
    {
        $ts = new \DateTime();

        return [
          [$ts, $ts->format(\DateTimeInterface::ATOM)],
          ['some string', 'some string']
        ];
    }

    /**
    * @dataProvider expiryDataProvider
    */
    public function testInitiatePaymentRequestsUrlForPaymentWithExpiryDate($sending, $sent): void
    {
        $url = 'http://some/url';
        $response = getRedirectResponse($url);
        $http = new MockHttpClient([$response]);
        $init = [...DEFAULTS, 'expiry' => $sending];

        $client = new Client('key');
        (fn () => $this->client = $http)->call($client);
        $this->assertSame($client->initiatePayment($init), $url);

        $this->assertSame($response->getRequestMethod(), 'POST');
        $this->assertSame($response->getRequestUrl(), 'https://pay.ponchopay.com/api/integration/generic/initiate');

        $options = $response->getRequestOptions();
        $this->assertContains('content-type: application/json', $options['headers']);
        $this->assertSame($options['body'], json_encode([...$init, 'expiry' => $sent, 'token' => TOKEN]));
    }

    public function testInitiatePaymentFailsIfResponseFromServerIsNotRedirect(): void
    {
        $response = new MockResponse('all good?');
        $http = new MockHttpClient([$response]);

        $client = new Client('key');
        (fn () => $this->client = $http)->call($client);
        $this->expectException(PonchoPayException::class);
        $client->initiatePayment(DEFAULTS);

        $this->assertSame($response->getRequestMethod(), 'POST');
        $this->assertSame($response->getRequestUrl(), 'https://pay.ponchopay.com/api/integration/generic/initiate');

        $options = $response->getRequestOptions();
        $this->assertContains('content-type: application/json', $options['headers']);
        $this->assertSame($options['body'], json_encode([...DEFAULTS, 'token' => TOKEN]));
    }
}
