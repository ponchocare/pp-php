# validateLocationUrn

This method allows you to validate a location to ensure it is ready to process payments.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$validation = $client->validateLocationUrn($payload);
```

## Usage

Declaration:

```php
$client->validateLocationUrn(array $payload): object;
```

Parameters:

| Parameter | Description                                                |
| --------- | ---------------------------------------------------------- |
| payload   | Parameters for the validation (Check `JWTPayload` details) |

JWTPayload:

| Parameter | Mandatory | Type   | Description                                                       |
| --------- | --------- | ------ | ----------------------------------------------------------------- |
| urn       | Yes       | string | The location Unique Reference Number                              |
| email     | Yes       | string | Email authoring the request (doesn't need to be a signed in user) |

Returns:
An object with the following properties:

| Property                            | Type    | Description                                                    |
| ----------------------------------- | ------- | -------------------------------------------------------------- |
| verification_status                 | boolean | Whether the location is verified and ready to process payments |
| card_payments_enabled               | boolean | Whether the location can accept card/bank payments             |
| childcare_voucher_payments_enabled  | boolean | Whether the location can accept childcare voucher payments     |
| tax_free_childcare_payments_enabled | boolean | Whether the location can accept tax-free childcare payments    |
