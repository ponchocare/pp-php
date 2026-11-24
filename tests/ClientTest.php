<?php

declare(strict_types=1);

namespace PonchoPay\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PonchoPay\Api;
use PonchoPay\Client;
use PonchoPay\PonchoPayException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function PonchoPay\createToken;
use function PonchoPay\Utils\serialise;

/**
 * @internal
 *
 * @covers \PonchoPay\\Client
 */
class ClientTest extends TestCase
{
    private static $URN = 'IUpGPArQ';
    private static $EMAIL = 'help@ponchopay.com';
    private static $KEY = '6N28tFbrufnfCT58ZvmzIwaL8S1aVFryIasJazFqdc516T/1ZrLw7CDqOSlF5NeF';
    private static $LOCATION = 'https://returned/location';

    public function testValidateLocationUrn(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn('{"some":"data"}');

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePutRequest');
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makeGetRequest')->with(
            '/api/integration/validate',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $response = $client->validateLocationUrn(['urn' => self::$URN, 'email' => self::$EMAIL]);
        $this->assertIsObject($response);
    }

    #[DataProvider('initiatePaymentProvider')]
    public function testInitiatePayment(array $init): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(302);
        $response->method('getHeaders')->with(false)->willReturn(['location' => [self::$LOCATION]]);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/initiate',
            [],
            serialise([...$init, 'token' => 'ytfBNCiHCbU/WdEZ1yEB60DsMpgD7VgR0SSqgJhj0mY='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY, $api);

        $location = $client->initiatePayment($init);
        $this->assertEquals(self::$LOCATION, $location);
    }

    public static function initiatePaymentProvider(): array
    {
        $defaults = [
            'metadata' => 'order-1234',
            'urn' => self::$URN,
            'amount' => 1234,
            'email' => self::$EMAIL,
        ];

        return [
            'with all the mandatory values' => [$defaults],
            'with a note' => [[...$defaults, 'note' => 'order note']],
            'with an expiry date' => [[...$defaults, 'date' => '2025-04-03T12:57:16.000Z']],
            'with a minimum card amount' => [[...$defaults, 'constraints' => ['minimum_card_amount' => 56]]],
            'with line items' => [[...$defaults, 'line_items' => [
                ['description' => 'item 1', 'amount' => 1000, 'quantity' => 1],
                ['description' => 'item 2', 'amount' => 234, 'quantity' => 1],
            ]]],
        ];
    }

    public function testInitiatePaymentAcceptsNonStringMetadata(): void
    {
        $init = ['metadata' => 1234, 'urn' => self::$URN, 'amount' => 1234, 'email' => self::$EMAIL];

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(302);
        $response->method('getHeaders')->with(false)->willReturn(['location' => [self::$LOCATION]]);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/initiate',
            [],
            serialise([...$init, 'token' => createToken(self::$KEY, '1234')])
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $location = $client->initiatePayment($init);
        $this->assertEquals(self::$LOCATION, $location);
    }

    public function testInitiatePaymentFailsForInvalidMetadata(): void
    {
        $init = ['metadata' => null, 'urn' => self::$URN, 'amount' => 1234, 'email' => self::$EMAIL];

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage("Invalid 'metadata' value. Expected a string.");
        $client->initiatePayment($init);
    }

    public function testInitiatePaymentFailsToResolveALocation(): void
    {
        $init = ['metadata' => 'order-1234', 'urn' => self::$URN, 'amount' => 1234, 'email' => self::$EMAIL];

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/initiate',
            [],
            serialise([...$init, 'token' => 'ytfBNCiHCbU/WdEZ1yEB60DsMpgD7VgR0SSqgJhj0mY='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected response. Expected 302 as status code but 200 was received');
        $client->initiatePayment($init);
    }

    #[DataProvider('initiateSubscriptionProvider')]
    public function testInitiateSubscription(array $init): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(302);
        $response->method('getHeaders')->with(false)->willReturn(['location' => [self::$LOCATION]]);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/subscription/redirect',
            [],
            serialise([...$init, 'token' => 'Axii0jwfzrkfh57WttOCi3qi+icnUC2dFKtre3b4pM4='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY, $api);

        $location = $client->initiateSubscription($init);
        $this->assertEquals(self::$LOCATION, $location);
    }

    public static function initiateSubscriptionProvider(): array
    {
        $defaults = [
            'metadata' => 'subscription-1234',
            'urn' => self::$URN,
            'amount' => 1234,
            'email' => self::$EMAIL,
            'repetition' => [
                'granularity' => 'weekly',
                'period' => 2,
                'weekdays' => ['tuesday', 'friday'],
            ],
        ];

        return [
            'with all the mandatory values' => [$defaults],
            'with a note' => [[...$defaults, 'note' => 'subscription note']],
            'with an ending' => [[...$defaults, 'ending' => ['condition' => 'date', 'date' => '2024-01-16T11:13:20.000Z']]],
            'with an additional one time payment' => [[...$defaults, 'additional_one_time_payment' => [
                'amount' => 678,
                'metadata' => 'order-5678',
            ]]],
        ];
    }

    public function testInitiateSubscriptionFailsToResolveALocation(): void
    {
        $init = [
            'metadata' => 'subscription-1234',
            'urn' => self::$URN,
            'amount' => 1234,
            'email' => self::$EMAIL,
            'repetition' => [
                'granularity' => 'week',
                'period' => 2,
                'weekdays' => ['tuesday', 'friday'],
            ],
        ];

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/subscription/redirect',
            [],
            serialise([...$init, 'token' => 'Axii0jwfzrkfh57WttOCi3qi+icnUC2dFKtre3b4pM4='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected response. Expected 302 as status code but 200 was received');
        $client->initiateSubscription($init);
    }

    #[DataProvider('updatePaymentMethodProvider')]
    public function testUpdatePaymentMethod(array $update): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/payment-method/cb35f971',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise($update)
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $client->updatePaymentMethod('cb35f971', [...$update, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public static function updatePaymentMethodProvider(): array
    {
        return [
            'change to card payment' => [['type' => 'card', 'amount' => 234]],
            'change to childcare voucher payment' => [['type' => 'childcare-voucher', 'amount' => 234, 'voucher_provider' => 'fun_for_kids']],
        ];
    }

    public function testUpdatePaymentMethodFailsTheRequest(): void
    {
        $update = ['type' => 'card', 'amount' => 234];

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/payment-method/cb35f971',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise($update)
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected response. Expected 204 as status code but 200 was received');
        $client->updatePaymentMethod('cb35f971', [...$update, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testRefundPaymentMethod(): void
    {
        $refund = ['amount' => 234];

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/payment-method/cb35f971/refund',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise($refund)
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $client->refundPaymentMethod('cb35f971', [...$refund, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testRefundPaymentMethodFailsTheRequest(): void
    {
        $refund = ['amount' => 234];

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/payment-method/cb35f971/refund',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise($refund)
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected response. Expected 204 as status code but 200 was received');
        $client->refundPaymentMethod('cb35f971', [...$refund, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testCancelPayment(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/payment/cb35f971/cancel',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise([])
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $client->cancelPayment('cb35f971', ['urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testCancelPaymentFailsTheRequest(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/payment/cb35f971/cancel',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise([])
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected response. Expected 204 as status code but 200 was received');
        $client->cancelPayment('cb35f971', ['urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testCancelRecursion(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/recursion/kgvkrglq/cancel',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise([])
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $client->cancelRecursion('kgvkrglq', ['urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testCancelRecursionFailsTheRequest(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->never())->method('makePostRequest');
        $api->expects($this->once())->method('makePutRequest')->with(
            '/api/recursion/kgvkrglq/cancel',
            $this->callback(function ($value) {
                $this->assertIsArray($value);
                $this->assertArrayHasKey('Authorization', $value);
                $this->assertIsString($value['Authorization']);

                return str_starts_with($value['Authorization'], 'Bearer ');
            }),
            serialise([])
        )->willReturn($response);

        $client = new Client(self::$KEY, $api);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected response. Expected 204 as status code but 200 was received');
        $client->cancelRecursion('kgvkrglq', ['urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    #[DataProvider('callbackProvider')]
    public function testIsValidCallback($signature, $payload, $valid, $_): void
    {
        $client = new Client(self::$KEY);
        $this->assertEquals($valid, $client->isValidCallback($signature, $payload));
    }

    #[DataProvider('callbackProvider')]
    public function testParseCallback($signature, $payload, $_, $error): void
    {
        $client = new Client(self::$KEY);

        if ($error) {
            $this->expectException(PonchoPayException::class);
            $this->expectExceptionMessage($error);
        }

        $result = $client->parseCallback($signature, $payload);
        if ($error) {
            $this->fail('An error was expected');
        } else {
            $this->assertEquals((object) ['some' => 'data'], $result);
        }
    }

    public static function callbackProvider(): array
    {
        return [
            '(string) valid signature and json' => ['6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M', '{"some":"data"}', true, null],
            '(string) valid signature and invalid json' => ['HwwwBECb1dK56MWhIQ_pffeKXZqGEglK9wIs-4IYJ2M', '{bad json}', true, 'Unexpected JSON value.'],
            '(string) invalid signature' => ['flop', '{"some":"data"}', false, 'Callback request failed the security signature'],

            '(stream) valid signature and json' => ['6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M', self::mockStream('{"some":"data"}', true), true, null],
            '(stream) valid signature and invalid json' => ['HwwwBECb1dK56MWhIQ_pffeKXZqGEglK9wIs-4IYJ2M', self::mockStream('{bad json}', true), true, 'Unexpected JSON value.'],
            '(stream) invalid signature' => ['flop', self::mockStream('{"some":"data"}', true), false, 'Callback request failed the security signature'],
        ];
    }

    public function testIsValidCallbackAcceptsShortReadsUnderLimit(): void
    {
        $signature = '6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M';
        $payload = self::mockChunkedStream(['{"some":', '"data"}']);

        $client = new Client(self::$KEY);

        $this->assertTrue($client->isValidCallback($signature, $payload));
    }

    public function testParseCallbackAcceptsShortReadsUnderLimit(): void
    {
        $signature = '6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M';
        $payload = self::mockChunkedStream(['{"some":', '"data"}']);

        $client = new Client(self::$KEY);

        $this->assertEquals((object) ['some' => 'data'], $client->parseCallback($signature, $payload));
    }

    public function testIsValidCallbackFailsForTooLongPayloads(): void
    {
        $signature = '6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M';
        $payload = self::mockStream('{"some":"data"}', false);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Stream too long. Expected a maximum of 2 MiB');
        $client->isValidCallback($signature, $payload);
    }

    public function testParseCallbackFailsForTooLongPayloads(): void
    {
        $signature = '6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M';
        $payload = self::mockStream('{"some":"data"}', false);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Stream too long. Expected a maximum of 2 MiB');

        $client->parseCallback($signature, $payload);
        $this->fail('An error was expected');
    }

    #[DataProvider('requestProvider')]
    public function testIsValidRequest($request, $valid, $_): void
    {
        $client = new Client(self::$KEY);
        $this->assertEquals($valid, $client->isValidRequest($request));
    }

    #[DataProvider('requestProvider')]
    public function testParseRequest($request, $_, $error): void
    {
        $client = new Client(self::$KEY);

        if ($error) {
            $this->expectException(PonchoPayException::class);
            $this->expectExceptionMessage($error);
        }

        $result = $client->parseRequest($request);
        if ($error) {
            $this->fail('An error was expected');
        } else {
            $this->assertEquals((object) ['some' => 'data'], $result);
        }
    }

    public static function requestProvider(): array
    {
        // Remove the `(string)` scenarios and compose a request from the others' variables
        return array_map(
            static function ($scenario) {
                [$signature, $payload, $valid, $error] = $scenario;

                return [self::mockRequest(['signature' => [$signature]], $payload), $valid, $error];
            },
            array_filter(
                self::callbackProvider(),
                static fn ($key) => !str_starts_with($key, '(string)'),
                ARRAY_FILTER_USE_KEY,
            )
        );
    }

    public function testIsValidRequestFailsForTooLongPayloads(): void
    {
        $payload = self::mockStream('{"some":"data"}', false);
        $request = self::mockRequest(['signature' => ['6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M']], $payload);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Stream too long. Expected a maximum of 2 MiB');
        $client->isValidRequest($request);
    }

    public function testIsValidRequestFailsForRequestsWithoutSignature(): void
    {
        $payload = self::mockStream('{"some":"data"}', true);
        $request = self::mockRequest([], $payload);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Signature header not found');
        $client->isValidRequest($request);
    }

    public function testIsValidRequestFailsForRequestsWithMultipleSignatures(): void
    {
        $payload = self::mockStream('{"some":"data"}', true);
        $request = self::mockRequest(['signature' => ['6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M', '6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M']], $payload);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Multiple signature headers found');
        $client->isValidRequest($request);
    }

    public function testParseRequestFailsForTooLongPayloads(): void
    {
        $payload = self::mockStream('{"some":"data"}', false);
        $request = self::mockRequest(['signature' => ['6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M']], $payload);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Stream too long. Expected a maximum of 2 MiB');

        $client->parseRequest($request);
        $this->fail('An error was expected');
    }

    public function testParseRequestFailsForRequestsWithoutSignature(): void
    {
        $payload = self::mockStream('{"some":"data"}', true);
        $request = self::mockRequest([], $payload);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Signature header not found');

        $client->parseRequest($request);
        $this->fail('An error was expected');
    }

    public function testParseRequestFailsForRequestsWithMultipleSignatures(): void
    {
        $payload = self::mockStream('{"some":"data"}', true);
        $request = self::mockRequest(['signature' => ['6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M', '6A54sGX71ohB9F4P05KYXBcQY2Bgob9o1WcGaCxcy1M']], $payload);

        $client = new Client(self::$KEY);

        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Multiple signature headers found');

        $client->parseRequest($request);
        $this->fail('An error was expected');
    }

    private static function mockRequest(array $headers, StreamInterface $body): RequestInterface
    {
        $request = self::createStub(RequestInterface::class);
        $request->method('getBody')->willReturn($body);
        foreach ($headers as $header => $value) {
            $request->method('getHeader')->with($header)->willReturn($value);
        }

        return $request;
    }

    private static function mockStream(string $data, bool $finished): StreamInterface
    {
        $stream = self::createStub(StreamInterface::class);
        $read = false;
        $stream->method('read')->with(self::isInt())->willReturnCallback(
            static function (int $length) use ($data, $finished, &$read): string {
                if ($read) {
                    return $finished ? '' : str_repeat('x', $length);
                }
                $read = true;

                return $finished ? $data : str_repeat('x', $length);
            },
        );
        $stream->method('eof')->willReturnCallback(
            static function () use ($finished, &$read): bool {
                return $finished && $read;
            },
        );
        $stream->method('isSeekable')->willReturn(false);

        return $stream;
    }

    /**
     * @param list<string> $chunks
     */
    private static function mockChunkedStream(array $chunks): StreamInterface
    {
        $stream = self::createStub(StreamInterface::class);
        $index = 0;
        $stream->method('read')->willReturnCallback(static function () use (&$index, $chunks): string {
            $chunk = $chunks[$index] ?? '';
            ++$index;

            return $chunk;
        });
        $stream->method('eof')->willReturnCallback(static function () use (&$index, $chunks): bool {
            return $index >= count($chunks);
        });
        $stream->method('isSeekable')->willReturn(false);

        return $stream;
    }
}
