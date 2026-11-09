<?php

declare(strict_types=1);

namespace PonchoPay\Tests\Utils;

use PHPUnit\Framework\TestCase;
use PonchoPay\PonchoPayException;

use function PonchoPay\Utils\deserialise;
use function PonchoPay\Utils\joinPaths;
use function PonchoPay\Utils\replaceParams;
use function PonchoPay\Utils\serialise;
use function PonchoPay\Utils\sha256;
use function PonchoPay\Utils\sha256Hmac;
use function PonchoPay\Utils\telemetry;

/**
 * @internal
 *
 * @coversNothing
 */
final class UtilsTest extends TestCase
{
    public function testSha256(): void
    {
        self::assertSame(
            'LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ=',
            sha256('hello')
        );
    }

    public function testSha256Hmac(): void
    {
        self::assertSame(
            'iKqz7ejTrflNJquQ07r9SiCDBww7zOnAFO4EpEOEfAs',
            sha256Hmac('secret', 'hello')
        );
    }

    public function testJoinPaths(): void
    {
        self::assertSame('/foo/bar', joinPaths('/foo', 'bar'));
        self::assertSame('/foo/bar', joinPaths('/foo/', 'bar'));
        self::assertSame('/foo/bar', joinPaths('/foo', '/bar'));
        self::assertSame('/foo/bar', joinPaths('/foo/', '/bar'));
    }

    public function testSerialise(): void
    {
        self::assertSame(
            '{"foo":"bar","number":123}',
            serialise(['foo' => 'bar', 'number' => 123])
        );
    }

    public function testSerialiseDateTime(): void
    {
        $date = new \DateTimeImmutable('2026-01-02T03:04:05+00:00');

        self::assertSame(
            '"2026-01-02T03:04:05+00:00"',
            serialise($date)
        );
    }

    public function testSerialiseNestedDateTime(): void
    {
        $date = new \DateTimeImmutable('2026-01-02T03:04:05+00:00');

        self::assertSame(
            '{"date":"2026-01-02T03:04:05+00:00"}',
            serialise(['date' => $date])
        );
    }

    public function testSerialiseEmptyArray(): void
    {
        self::assertSame('', serialise([]));
    }

    public function testDeserialise(): void
    {
        $result = deserialise('{"foo":"bar","number":123}');

        self::assertSame('bar', $result->foo);
        self::assertSame(123, $result->number);
    }

    public function testDeserialiseThrowsForMalformedJson(): void
    {
        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected JSON value.');

        deserialise('{invalid json');
    }

    public function testDeserialiseThrowsWhenJsonIsNotAnObject(): void
    {
        $this->expectException(PonchoPayException::class);
        $this->expectExceptionMessage('Unexpected JSON value.');

        deserialise('["foo", "bar"]');
    }

    public function testReplaceParams(): void
    {
        self::assertSame(
            '/users/123/orders/456',
            replaceParams(
                '/users/[user id]/orders/[order id]',
                [
                    'user id' => '123',
                    'order id' => '456',
                ]
            )
        );
    }

    public function testReplaceParamsRemovesUnknownParams(): void
    {
        self::assertSame(
            '/users/123/orders/',
            replaceParams(
                '/users/[user id]/orders/[order id]',
                ['user id' => '123']
            )
        );
    }

    public function testReplaceParamsLeavesNonMatchingPlaceholdersUntouched(): void
    {
        self::assertSame(
            '/users/{user}/orders/:order',
            replaceParams('/users/{user}/orders/:order', [
                'user' => '123',
                'order' => '456',
            ])
        );
    }

    public function testTelemetry(): void
    {
        $telemetry = telemetry();

        self::assertSame(
            [
                'vendor' => 'poncho',
                'name' => 'ponchopay/pp-php',
            ],
            array_intersect_key($telemetry['package'], [
                'vendor' => true,
                'name' => true,
            ])
        );

        self::assertSame('php', $telemetry['environment']['runtime']);
        self::assertSame(php_uname('m'), $telemetry['environment']['arch']);
        self::assertSame(PHP_OS, $telemetry['environment']['platform']);
        self::assertSame(phpversion(), $telemetry['environment']['version']);

        self::assertIsString($telemetry['package']['version']);
    }
}
