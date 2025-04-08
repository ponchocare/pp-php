<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use function PonchoPay\createToken;
use function PonchoPay\createJWT;

class TokenTest extends TestCase
{
    private static $URN = 'IUXfYEwc';
    private static $KEY = 'oIUjW4n39vKZXpNQJQALbEW9oQ69GUVmOx43J/+/o6SHLlM9kCAkgM0bdd9WjoX9';
    private static $EMAIL = 'help@ponchopay.com';
    private static $METADATA = '{"order":"7ee5422c"}';
    private static $DATA = '{"amount":123}';


    public function testCreateToken(): void
    {
        $this->assertSame(createToken(self::$KEY, self::$METADATA), 'M1p0UAdLLxavwVrmfRStkSvAxwzpMGjpxFjVdxBquFc=');
    }

    public function testCreateJWT(): void
    {
        $jwt = createJWT(self::$URN, self::$KEY, self::$EMAIL, self::$DATA);
        $this->assertIsString($jwt);

        $decoded = JWT::decode($jwt, new Key(hash('sha256', self::$KEY, true), 'HS256'));
        $this->assertIsObject($decoded);
        $this->assertObjectHasProperty('urn', $decoded);
        $this->assertEquals(self::$URN, $decoded->urn);
        $this->assertObjectHasProperty('email', $decoded);
        $this->assertEquals(self::$EMAIL, $decoded->email);
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertIsInt($decoded->iat);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertIsInt($decoded->exp);
        $this->assertObjectHasProperty('sig', $decoded);
        $this->assertEquals('gy94YzQircdzlf5hIcYBZnddVWQfjp9sBYTe/LuoyQc=', $decoded->sig);
    }
}
