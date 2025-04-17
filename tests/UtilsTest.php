<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function PonchoPay\Utils\joinPaths;
use function PonchoPay\Utils\serialise;
use function PonchoPay\Utils\replaceParams;
use function PonchoPay\Utils\telemetry;

class UtilsTest extends TestCase
{
    public static function joinPathsProvider(): array
    {
        return [
            'adding forward slash in the middle'  => ['left/path', 'right/path'],
            'sanitising left path first'  => ['left/path///', 'right/path'],
            'sanitising right path first'  => ['left/path', '///right/path'],
        ];
    }

    #[DataProvider('joinPathsProvider')]
    public function testJoinsPaths(string $leftPath, string $rightPath): void
    {
        $result = joinPaths($leftPath, $rightPath);
        $this->assertEquals('left/path/right/path', $result);
    }

    public static function serialiseProvider(): array
    {
        return [
            'strings'  => ['data', '"data"'],
            'numbers'  => [123, 123],
            'dates' => [\DateTime::createFromFormat('U', 1744115983), '"2025-04-08T12:39:43+00:00"'],
            'associative array' => [['key1' => 'value1', 'key2' => 456], '{"key1":"value1","key2":456}'],
            'empty array' => [[], '']
        ];
    }

    #[DataProvider('serialiseProvider')]
    public function testSerialise(mixed $input, mixed $output): void
    {
        $this->assertEquals($output, serialise($input));
    }


    public static function replaceParamsProvider(): array
    {
        return [
            'no replacement'  => ['some data', 'some data'],
            'one parameter replaced once'  => ['this [is] data', 'this was data'],
            'one parameter replaced multiple times' => ['th[is] [is] data', 'thwas was data'],
            'multiple parameters replaced' => ['this [is] [data]', 'this was payload'],
            'parameter removed' => ['[this is ]data', 'data']
        ];
    }

    #[DataProvider('replaceParamsProvider')]
    public function testReplaceParams(mixed $input, mixed $output): void
    {
        $this->assertEquals($output, replaceParams($input, [
          'is' => 'was',
          'data' => 'payload'
        ]));
    }

    public function testTelemetry(): void
    {
        $telemetry = telemetry();
        $this->assertIsArray($telemetry);
        $this->assertArrayHasKey('package', $telemetry);
        $this->assertArrayHasKey('environment', $telemetry);
    }
}
