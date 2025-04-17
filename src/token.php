<?php

namespace PonchoPay;

use Firebase\JWT\JWT;

/**
 * Creates a token for initialisation endpoints.
 */
function createToken(string $key, string $metadata): string
{
    return base64_encode(hash('sha256', $metadata . '.' .$key, true));
}

/**
 * Creates a token for manipulation endpoints.
 */
function createJWT(string $urn, string $key, string $email, string $data): string
{
    $now = time();
    return JWT::encode([
        'urn' => $urn,
        'email' => $email,
        'iat' => $now,
        'exp' => $now + 5,
        'sig' => base64_encode(hash('sha256', $data, true)),
    ], hash('sha256', $key, true), 'HS256');
}
