# isValidRequest

This method allows to validate a request received from PonchoPay.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$client->isValidRequest($request);
```

## Usage

Declaration:

```php
$client->isValidRequest(RequestInterface $request): bool;
```

Parameters:

| Parameter | Description                                       |
| --------- | ------------------------------------------------- |
| request   | The compatible RequestInterface object from PSR-7 |


Returns:

This method returns `true` or `false` depending on whether the request was created by PonchoPay or not.
