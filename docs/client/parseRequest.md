# parseRequest

This method allows to parse a request received from PonchoPay.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$client->parseRequest($request);
```

## Usage

Declaration:

```php
$client->parseRequest(RequestInterface $request): object;
```

Parameters:

| Parameter | Description                                       |
| --------- | ------------------------------------------------- |
| request   | The compatible RequestInterface object from PSR-7 |


Returns:

The deserialised JSON object sent from PonchoPay. An error will be thrown if the request was not sent by PonchoPay.
