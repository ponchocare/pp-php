<?php

namespace PonchoPay\Utils;

use Composer\InstalledVersions;

define('PONCHOPAY_PACKAGE', "ponchopay/pp-php");

/**
 * Joins two paths with a single forward slash in the middle
 */
function joinPaths(string $left, string $right): string
{
    return rtrim($left, '/') . '/' . ltrim($right, '/');
}

/**
 * JSON-stringifies some data.
 */
function serialise(mixed $data): string
{
    $process = function (mixed $value) use (&$process): mixed {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return array_map($process, $value);
        }

        return $value;
    };

    if (is_array($data) && count($data) === 0) {
        return '';
    }

    return json_encode($process($data));
}

/**
 * Replaces any occurrence of [param] in the haystack with the corresponding value from params
 */
function replaceParams(string $haystack, array $params): string
{
    $replace = function ($matches) use ($params): string {
        return $params[$matches[1]] ?? '';
    };

    return preg_replace_callback('/\[([a-zA-Z ]+)\]/', $replace, $haystack);
}

/**
 * Returns some anonymous environment parameters.
 * Those values are used to improve the service.
 */
function telemetry(): array
{
    $version = InstalledVersions::getPrettyVersion(PONCHOPAY_PACKAGE);

    return [
        'package' => ['vendor' => 'poncho', 'name' => PONCHOPAY_PACKAGE, 'version' => $version ],
        'environment' => ['runtime' => 'php', 'arch' => php_uname('m'), 'platform' => PHP_OS, 'version' => phpversion()]
    ];
}
