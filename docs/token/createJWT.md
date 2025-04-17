# createJWT

This function allows the creation of JWTs for the payment manipulation endpoints.

## How to

Import the function and use it by passing the relevant parameters:

```php
use function PonchoPay\createJWT;

$jwt = createJWT($urn, $key, $email, $data);
```

## Usage

Declaration:

```ts
createJWT(string $urn, string $key, string $email, string $data): string;
```

Parameters:

| Parameter | Description                                                         |
| --------- | ------------------------------------------------------------------- |
| urn       | The Unique Reference Number                                         |
| key       | Integration key                                                     |
| email     | Email authoring the sent data (doesn't need to be a signed in user) |
| data      | The data to be sent                                                 |

Returns:

This function returns the JWT to be sent to PonchoPay for the request authorisation.
