# pp-php

Tools to integrate PonchoPay on PHP

## Installation

Install it from `packagist`:

```bash
composer require ponchopay/pp-php
```

## Usage

This package declares 2 objects:

### createToken: function

Importing:

```php
use function PonchoPay\createToken;
```

Parameters:

| Parameter | Mandatory | Type   | Description                                          |
| --------- | --------- | ------ | ---------------------------------------------------- |
| key       | Yes       | string | Integration key                                      |
| metadata  | Yes       | string | Any string you want to keep saved within the payment |

Returns:
It returns an string containing the token to be used to initate a payment

### Client: class

Importing:

```php
use PonchoPay\Client;
```

Constructor:

| Parameter | Mandatory | Type   | Description                                                    |
| --------- | --------- | ------ | -------------------------------------------------------------- |
| key       | Yes       | string | Integration key                                                |
| base      | No        | string | Base PonchoPay URL to use. Default: https://pay.ponchopay.com/ |

Methods:

**initiatePayment**:

Parameters:

| Parameter | Mandatory | Type  | Description                   |
| --------- | --------- | ----- | ----------------------------- |
| init      | Yes       | array | Payment initialisation object |

The payment initialisation associative array is defined as follows:

| Parameter | Mandatory | Type   | Description                                          |
| --------- | --------- | ------ | ---------------------------------------------------- |
| metadata  | Yes       | string | Any string you want to keep saved within the payment |
| urn       | Yes       | string | The Unique Reference Number                          |
| amount    | Yes       | number | The payable amount in pences                         |
| email     | Yes       | string | The user email                                       |
| note      | No        | string | Any note to be attached to the payment               |

Returns:
It returns the URL the user needs to use to make the payment.

## Development

### Linting and formatting

To automatically fix linting and formatting errors, run

```bash
vendor/bin/php-cs-fixer fix .
```

### Testing

To ensure the project is bug-free, run

```bash
vendor/bin/phpunit tests
```
