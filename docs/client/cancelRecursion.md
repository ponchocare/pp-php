# cancelRecursion

This method allows to cancel a Recurring Payment.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$client->cancelRecursion($recursionId, $payload);
```

## Usage

Declaration:

```php
$client->cancelRecursion(string $recursionId, array $payload): void;
```

Parameters:

| Parameter   | Description                                                        |
| ----------- | ------------------------------------------------------------------ |
| recursionId | Recursion ID. You can find this in the callbacks' body             |
| payload     | Parameters for the cancel to happen (Check `JWTPayload` details)   |

JWTPayload:

| Parameter | Mandatory | Type   | Description                                                      |
| --------- | --------- | ------ | ---------------------------------------------------------------- |
| urn       | Yes       | string | The location Unique Reference Number                             |
| email     | Yes       | string | Email authoring the update (doesn't need to be a signed in user) |

Returns:

This method doesn't return a usable value.
