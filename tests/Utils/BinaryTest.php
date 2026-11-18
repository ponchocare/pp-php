<?php

declare(strict_types=1);

namespace PonchoPay\Tests\Utils;

use PHPUnit\Framework\TestCase;
use PonchoPay\Utils\Binary;

/**
 * @internal
 *
 * @coversNothing
 */
final class BinaryTest extends TestCase
{
    public function testFromBytes(): void
    {
        self::assertSame(123, Binary::fromBytes(123)->toBytes());
    }

    public function testFromKibibytes(): void
    {
        self::assertSame(2048, Binary::fromKibibytes(2)->toBytes());
    }

    public function testFromMebibytes(): void
    {
        self::assertSame(3145728, Binary::fromMebibytes(3)->toBytes());
    }
}
