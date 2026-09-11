<?php

namespace PonchoPay;

/**
 * Constructs a new PonchoPayException. This is the custom exception class used
 * throughout the library to wrap and clearly prefix standard PHP exceptions
 * with the "PonchoPay" namespace identifier.
 */
final class PonchoPayException extends \Exception
{
    /**
     * @param string $message the specific error message that occurred during the operation
     */
    public function __construct(string $message)
    {
        parent::__construct("[PonchoPay] {$message}");
    }
}
