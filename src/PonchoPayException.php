<?php

namespace PonchoPay;

use Exception;

final class PonchoPayException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct("[PonchoPay] $message");
    }
}
