<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use PonchoPay\Api;

class ApiTest extends TestCase
{
    private static $PROD = 'pay.ponchopay.com';
    private static $DEMO = 'demo.ponchopay.com';
    private static $PATH = '/endpoint/path';
    private static $HEADERS = ['Accept-Language' => 'en'];
    private static $BODY = '{"payment":"9207f21a","amount":123}';

    public static function basesProvider(): array
    {
        return [
            'default base'  => [[], self::$PROD],
            'demo base'  => [['https://' . self::$DEMO . '/'], self::$DEMO],
        ];
    }

    #[DataProvider('basesProvider')]
    public function testMakePostRequest(array $args, string $base): void
    {
        $expectedResponse = $this->createMock(ResponseInterface::class);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())->method('request')->with(
            'POST',
            'https://' . $base . self::$PATH,
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

        $api = new Api(...$args);
        (fn () => $this->client = $client)->call($api);

        $response = $api->makePostRequest(self::$PATH, self::$HEADERS, self::$BODY);
        $this->assertEquals($expectedResponse, $response);
    }

    #[DataProvider('basesProvider')]
    public function testMakePutRequest(array $args, string $base): void
    {
        $expectedResponse = $this->createMock(ResponseInterface::class);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())->method('request')->with(
            'PUT',
            'https://' . $base . self::$PATH,
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

        $api = new Api(...$args);
        (fn () => $this->client = $client)->call($api);

        $response = $api->makePutRequest(self::$PATH, self::$HEADERS, self::$BODY);
        $this->assertEquals($expectedResponse, $response);
    }
}
