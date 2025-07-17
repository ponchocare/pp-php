# pp-php

Tools to integrate PonchoPay on PHP

## Installation

Install it from `packagist`:

```bash
composer require ponchopay/pp-php
```

## Usage

This package declares the following functions:
- [createToken](https://github.com/ponchocare/pp-php/blob/master/docs/token/createToken.md): This function allows the creation of tokens for the initialisation endpoints.
- [createJWT](https://github.com/ponchocare/pp-php/blob/master/docs/token/createJWT.md): This function allows the creation of JWTs for the payment manipulation endpoints.

And the `Client` class which provides the following methods:
- [initiatePayment](https://github.com/ponchocare/pp-php/blob/master/docs/client/initiatePayment.md): This method allows to initialise a payment in PonchoPay.
- [initiateSubscription](https://github.com/ponchocare/pp-php/blob/master/docs/client/initiateSubscription.md): This method allows to initialise a subscription in PonchoPay.
- [updatePaymentMethod](https://github.com/ponchocare/pp-php/blob/master/docs/client/updatePaymentMethod.md): This method allows to change the Payment Method properties.
- [refundPaymentMethod](https://github.com/ponchocare/pp-php/blob/master/docs/client/refundPaymentMethod.md): This method allows to fully or partially refund a Payment Method.
- [cancelPayment](https://github.com/ponchocare/pp-php/blob/master/docs/client/cancelPayment.md): This method allows to cancel a Payment.
- [cancelRecursion](https://github.com/ponchocare/pp-php/blob/master/docs/client/cancelRecursion.md): This method allows to cancel a Recurring Payment.

## Development

### Linting and formatting

To automatically fix linting and formatting errors, run

```bash
composer exec php-cs-fixer fix .
```

### Testing

To ensure the project is bug-free, run

```bash
composer exec phpunit tests/
```
