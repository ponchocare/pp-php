<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;
use PonchoPay\Api;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @covers \PonchoPay\Api
 */
class ApiTest extends TestCase
{
    private static $PROD = 'pay.ponchopay.com';
    private static $DEMO = 'demo.ponchopay.com';
    private static $PATH = '/endpoint/path';
    private static $HEADERS = ['Accept-Language' => 'en'];
    private static $BODY = '{"payment":"9207f21a","amount":123}';

    public function testMakeGetRequest(): void
    {
        $expectedResponse = $this->createStub(ResponseInterface::class);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())->method('request')->with(
            'GET',
            'proto://base'.self::$PATH,
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('headers', $value);
                $this->assertArrayHasKey('Accept-Language', $value['headers']);
                $this->assertEquals('en', $value['headers']['Accept-Language']);
                $this->assertArrayHasKey('content-type', $value['headers']);
                $this->assertEquals('application/json', $value['headers']['content-type']);
                $this->assertArrayHasKey('x-telemetry', $value['headers']);
                $this->assertIsString($value['headers']['x-telemetry']);
                $this->assertArrayHasKey('body', $value);
                $this->assertEquals('', $value['body']);
                $this->assertArrayHasKey('max_redirects', $value);
                $this->assertEquals(0, $value['max_redirects']);

                return true;
            })
        )->willReturn($expectedResponse);

        $api = new Api('proto://base');
        (fn () => $this->client = $client)->call($api);

        $response = $api->makeGetRequest(self::$PATH, self::$HEADERS);
        $this->assertEquals($response, $expectedResponse);
    }

    public function testMakePostRequest(): void
    {
        $expectedResponse = $this->createStub(ResponseInterface::class);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())->method('request')->with(
            'POST',
            'proto://base'.self::$PATH,
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('headers', $value);
                $this->assertArrayHasKey('Accept-Language', $value['headers']);
                $this->assertEquals('en', $value['headers']['Accept-Language']);
                $this->assertArrayHasKey('content-type', $value['headers']);
                $this->assertEquals('application/json', $value['headers']['content-type']);
                $this->assertArrayHasKey('x-telemetry', $value['headers']);
                $this->assertIsString($value['headers']['x-telemetry']);
                $this->assertArrayHasKey('body', $value);
                $this->assertEquals(self::$BODY, $value['body']);
                $this->assertArrayHasKey('max_redirects', $value);
                $this->assertEquals(0, $value['max_redirects']);

                return true;
            })
        )->willReturn($expectedResponse);

        $api = new Api('proto://base');
        (fn () => $this->client = $client)->call($api);

        $response = $api->makePostRequest(self::$PATH, self::$HEADERS, self::$BODY);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testMakePutRequest(): void
    {
        $expectedResponse = $this->createStub(ResponseInterface::class);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())->method('request')->with(
            'PUT',
            'proto://base'.self::$PATH,
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('headers', $value);
                $this->assertArrayHasKey('Accept-Language', $value['headers']);
                $this->assertEquals('en', $value['headers']['Accept-Language']);
                $this->assertArrayHasKey('content-type', $value['headers']);
                $this->assertEquals('application/json', $value['headers']['content-type']);
                $this->assertArrayHasKey('x-telemetry', $value['headers']);
                $this->assertIsString($value['headers']['x-telemetry']);
                $this->assertArrayHasKey('body', $value);
                $this->assertEquals(self::$BODY, $value['body']);
                $this->assertArrayHasKey('max_redirects', $value);
                $this->assertEquals(0, $value['max_redirects']);

                return true;
            })
        )->willReturn($expectedResponse);

        $api = new Api('proto://base');
        (fn () => $this->client = $client)->call($api);

        $response = $api->makePutRequest(self::$PATH, self::$HEADERS, self::$BODY);
        $this->assertEquals($expectedResponse, $response);
    }
}
