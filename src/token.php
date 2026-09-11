<?php

namespace PonchoPay;

use Firebase\JWT\JWT;

use function PonchoPay\Utils\sha256;

/**
 * Generates a simple, fixed-format token string used for basic request authentication,
 * combining the provided metadata and API key.
 *
 * @param string $key      the integration key found in the https://pay.ponchopay.com/provider/admin/settings/api-integration page
 * @param string $metadata additional contextual data used in token creation
 *
 * @return string the generated token string
 */
function createToken(string $key, string $metadata): string
{
    return sha256($metadata.'.'.$key);
}

/**
 * Generates a JSON Web Token (JWT) containing essential request metadata (URN, email, etc.)
 * and signs it using the API key to ensure authenticity for API calls.
 *
 * @param string $urn   the location URN associated with the request
 * @param string $key   the integration key found in the https://pay.ponchopay.com/provider/admin/settings/api-integration page
 * @param string $email the email address associated with the request
 * @param string $data  the specific payload data that needs to be included in the token signature
 *
 * @return string the fully encoded and signed JWT string
 */
function createJWT(string $urn, string $key, string $email, string $data): string
{
    $now = time();

    return JWT::encode([
        'urn' => $urn,
        'email' => $email,
        'iat' => $now,
        'exp' => $now + 5,
        'sig' => sha256($data),
    ], hash('sha256', $key, true), 'HS256');
}
