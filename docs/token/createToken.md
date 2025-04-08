# createToken

This function allows the creation of tokens for the initialisation endpoints.

## How to

Import the function and use it by passing the relevant parameters:

```php
use function PonchoPay\createToken;

$token = createToken($key, $metadata);
```

## Usage

Declaration:

```ts
createToken(string $key, string $metadata): string;
```

Parameters:

| Parameter | Description                                          |
| --------- | ---------------------------------------------------- |
| key       | Integration key                                      |
| metadata  | Any string you want to keep saved within the payment |

Returns:

This function returns the token to be sent to PonchoPay for the request authorisation.
