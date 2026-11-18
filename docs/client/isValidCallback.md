# isValidCallback

This method allows to validate a callback received from PonchoPay.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$client->isValidCallback($signature, $payload);
```

## Usage

Declaration:

```php
$client->isValidCallback(string $signature, StreamInterface|string $payload): bool;
```

Parameters:

| Parameter | Description                                                      |
| --------- | ---------------------------------------------------------------- |
| signature | The signature header sent by PonchoPay.                          |
| payload   | The raw payload as a StreamInterface PSR-7 object or as a string |


Returns:

This method returns `true` or `false` depending on whether the callback was created by PonchoPay or not.
