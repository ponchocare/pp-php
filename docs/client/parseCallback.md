# parseCallback

This method allows to parse a callback received from PonchoPay.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$client->parseCallback($signature, $payload);
```

## Usage

Declaration:

```php
$client->parseCallback(string $signature, StreamInterface|string $payload): bool;
```

Parameters:

| Parameter | Description                                                      |
| --------- | ---------------------------------------------------------------- |
| signature | The signature header sent by PonchoPay.                          |
| payload   | The raw payload as a StreamInterface PSR-7 object or as a string |


Returns:

The deserialised JSON object sent from PonchoPay. An error will be thrown if the callback was not sent by PonchoPay.
