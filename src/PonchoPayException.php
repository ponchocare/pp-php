<?php

namespace PonchoPay;

final class PonchoPayException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("[PonchoPay] {$message}");
    }
}
