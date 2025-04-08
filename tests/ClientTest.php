<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\HttpClient\ResponseInterface;
use PonchoPay\Client;
use PonchoPay\Api;
use PonchoPay\PonchoPayException;

use function PonchoPay\Utils\serialise;

class ClientTest extends TestCase
{
    private static $URN = 'IUpGPArQ';
    private static $EMAIL = 'help@ponchopay.com';
    private static $KEY = '6N28tFbrufnfCT58ZvmzIwaL8S1aVFryIasJazFqdc516T/1ZrLw7CDqOSlF5NeF';
    private static $LOCATION = 'https://returned/location';

    public static function initiatePaymentProvider(): array
    {
        $defaults = [
          'metadata' => 'order-1234',
          'urn' => self::$URN,
          'amount' => 1234,
          'email' => self::$EMAIL
        ];

        return [
            'with all the mandatory values'  => [$defaults],
            'with a note' => [[...$defaults, 'note' => 'order note']],
            'with an expiry date' => [[...$defaults, 'date' => '2025-04-03T12:57:16.000Z']],
            'with a minimum card amount' => [[...$defaults, 'constraints' => ['minimum_card_amount' => 56]]]
        ];
    }

    #[DataProvider('initiatePaymentProvider')]
    public function testInitiatePayment(array $init): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(302);
        $response->method('getHeaders')->with(false)->willReturn(['location' => [self::$LOCATION]]);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/initiate',
            [],
            serialise([...$init, 'token' => 'ytfBNCiHCbU/WdEZ1yEB60DsMpgD7VgR0SSqgJhj0mY='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $location = $client->initiatePayment($init);
        $this->assertEquals(self::$LOCATION, $location);
    }

    public function testInitiatePaymentFailsToResolveALocation(): void
    {
        $init = ['metadata' => 'order-1234', 'urn' => self::$URN, 'amount' => 1234, 'email' => self::$EMAIL];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/initiate',
            [],
            serialise([...$init, 'token' => 'ytfBNCiHCbU/WdEZ1yEB60DsMpgD7VgR0SSqgJhj0mY='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $this->expectException(PonchoPayException::class);
        $client->initiatePayment($init);
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
            'weekdays' => ['tuesday', 'friday']
          ]
        ];

        return [
            'with all the mandatory values'  => [$defaults],
            'with a note' => [[...$defaults, 'note' => 'subscription note']],
            'with an ending' => [[...$defaults, 'ending' => ['condition' => 'date', 'date' => '2024-01-16T11:13:20.000Z']]],
            'with an additional one time payment' => [[...$defaults, 'additional_one_time_payment' => [
              'amount' => 678,
              'metadata' => 'order-5678',
            ]]]
        ];
    }

    #[DataProvider('initiateSubscriptionProvider')]
    public function testInitiateSubscription(array $init): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(302);
        $response->method('getHeaders')->with(false)->willReturn(['location' => [self::$LOCATION]]);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/subscription',
            [],
            serialise([...$init, 'token' => 'Axii0jwfzrkfh57WttOCi3qi+icnUC2dFKtre3b4pM4='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $location = $client->initiateSubscription($init);
        $this->assertEquals(self::$LOCATION, $location);
    }

    public function testInitiateSubscriptionFailsToResolveALocation(): void
    {
        $init = [
          'metadata' => 'subscription-1234',
          'urn' => self::$URN,
          'amount' => 1234,
          'email' => self::$EMAIL,
          'repetition' => [
            'granularity' => 'weekly',
            'period' => 2,
            'weekdays' => ['tuesday', 'friday']
          ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $api = $this->createMock(Api::class);
        $api->expects($this->once())->method('makePostRequest')->with(
            '/api/integration/generic/subscription',
            [],
            serialise([...$init, 'token' => 'Axii0jwfzrkfh57WttOCi3qi+icnUC2dFKtre3b4pM4='])
        )->willReturn($response);
        $api->expects($this->never())->method('makePutRequest');

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $this->expectException(PonchoPayException::class);
        $client->initiateSubscription($init);
    }

    public static function updatePaymentMethodProvider(): array
    {
        return [
            'change to card payment'  => [['type' => 'card', 'amount' => 234]],
            'change to childcare voucher payment'  => [['type' => 'childcare-voucher', 'amount' => 234, 'voucher_provider' => 'fun_for_kids']],
        ];
    }

    #[DataProvider('updatePaymentMethodProvider')]
    public function testUpdatePaymentMethod(array $update): void
    {
        $response = $this->createMock(ResponseInterface::class);
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

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $client->updatePaymentMethod('cb35f971', [...$update, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testUpdatePaymentMethodFailsTheRequest(): void
    {
        $update = ['type' => 'card', 'amount' => 234];

        $response = $this->createMock(ResponseInterface::class);
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

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $this->expectException(PonchoPayException::class);
        $client->updatePaymentMethod('cb35f971', [...$update, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testRefundPaymentMethod(): void
    {
        $refund = ['amount' => 234];

        $response = $this->createMock(ResponseInterface::class);
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

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $client->refundPaymentMethod('cb35f971', [...$refund, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testRefundPaymentMethodFailsTheRequest(): void
    {
        $refund = ['amount' => 234];

        $response = $this->createMock(ResponseInterface::class);
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

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $this->expectException(PonchoPayException::class);
        $client->refundPaymentMethod('cb35f971', [...$refund, 'urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testCancelPayment(): void
    {
        $response = $this->createMock(ResponseInterface::class);
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
            serialise([])
        )->willReturn($response);

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $client->refundPaymentMethod('cb35f971', ['urn' => self::$URN, 'email' => self::$EMAIL]);
    }

    public function testCancelPaymentFailsTheRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
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
            serialise([])
        )->willReturn($response);

        $client = new Client(self::$KEY);
        (fn () => $this->api = $api)->call($client);

        $this->expectException(PonchoPayException::class);
        $client->refundPaymentMethod('cb35f971', ['urn' => self::$URN, 'email' => self::$EMAIL]);
    }
}
