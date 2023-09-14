<?php

namespace PonchoPay;

function createToken(string $key, string $metadata): string
{
    return base64_encode(hash('sha256', "{$metadata}.{$key}", true));
}
