<?php

namespace PonchoPay\Utils;

use Composer\InstalledVersions;
use PonchoPay\PonchoPayException;

define('PONCHOPAY_PACKAGE', 'ponchopay/pp-php');

/**
 * Generates a simple SHA256 hash for a given string.
 *
 * @param string $data the input data to hash
 *
 * @return string the base64 encoded SHA256 hash
 */
function sha256(string $data): string
{
    return base64_encode(hash('sha256', $data, true));
}

/**
 * Generates a HMAC-SHA256 hash using a secret key and the provided data.
 *
 * @param string $key  the secret key used for HMAC generation
 * @param string $data the data to generate the HMAC hash for
 *
 * @return string the URL-safe base64 encoded HMAC-SHA256 hash
 */
function sha256Hmac(string $key, string $data): string
{
    return rtrim(strtr(base64_encode(hash_hmac('sha256', $data, $key, true)), '+/', '-_'), '=');
}

/**
 * Joins two URL paths in a manner that correctly handles leading and trailing slashes.
 *
 * @param string $left  the initial part of the path
 * @param string $right the subsequent part of the path
 *
 * @return string the correctly concatenated path
 */
function joinPaths(string $left, string $right): string
{
    return rtrim($left, '/').'/'.ltrim($right, '/');
}

/**
 * Serializes a PHP data structure into a JSON string format. It handles serialization
 * of objects like DateTimeInterface by converting them to an ISO 8601 string.
 *
 * @param mixed $data the data to serialize
 *
 * @return string the resulting JSON string
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

    if (is_array($data) && 0 === count($data)) {
        return '';
    }

    return json_encode($process($data));
}

/**
 * Deserializes a JSON string into a PHP object, performing basic validation checks.
 *
 * @param string $data the JSON string to decode
 *
 * @return object the resulting PHP object
 *
 * @throws PonchoPayException if the JSON is malformed or the result is not a valid object
 */
function deserialise(string $data): object
{
    try {
        $decoded = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
        throw new PonchoPayException('Unexpected JSON value.');
    }

    if (!\is_object($decoded)) {
        throw new PonchoPayException('Unexpected JSON value.');
    }

    return $decoded;
}

/**
 * Replaces placeholders in a path string (e.g., `[param]`) with actual values from an array.
 *
 * @param string                $haystack the string containing placeholders
 * @param array<string, string> $params   an associative array mapping placeholder names to replacement values
 *
 * @return string the path with all specified parameters correctly replaced
 */
function replaceParams(string $haystack, array $params): string
{
    $replace = function ($matches) use ($params): string {
        return $params[$matches[1]] ?? '';
    };

    return preg_replace_callback('/\[([a-zA-Z ]+)\]/', $replace, $haystack);
}

/**
 * Collects system and package version information to be used for telemetry tracking.
 *
 * @return array an array containing package and environment details
 */
function telemetry(): array
{
    $version = InstalledVersions::getPrettyVersion(PONCHOPAY_PACKAGE);

    return [
        'package' => ['vendor' => 'poncho', 'name' => PONCHOPAY_PACKAGE, 'version' => $version],
        'environment' => ['runtime' => 'php', 'arch' => php_uname('m'), 'platform' => PHP_OS, 'version' => phpversion()],
    ];
}
