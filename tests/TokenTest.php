<?php

namespace PonchoPay\Test;

use PHPUnit\Framework\TestCase;

use function PonchoPay\createToken;

class TokenTest extends TestCase
{
    public function testCreateToken(): void
    {
        $this->assertSame(createToken('key', 'metadata'), 'QUYCI7s3sDpIYvVcKojrKpQWZt+u3pp7O7E4Rdu+G1w=');
    }
}
