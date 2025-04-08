# initiatePayment

This method allows to initialise a payment in PonchoPay.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$payment = $client->initiatePayment($init);
```

## Usage

Declaration:

```php
$client->initiatePayment(array $init): string;
```

Parameters:

| Parameter | Description                                                |
| --------- | ---------------------------------------------------------- |
| init      | Payment initialisation array (Check `PaymentInit` details) |

PaymentInit:

| Parameter   | Mandatory | Type                        | Description                                                         |
| ----------- | --------- | --------------------------- | ------------------------------------------------------------------- |
| metadata    | Yes       | string                      | Any string you want to keep attached to the payment                 |
| urn         | Yes       | string                      | The location Unique Reference Number                                |
| amount      | Yes       | integer                     | The payable amount in pences                                        |
| email       | Yes       | string                      | The user email                                                      |
| note        | No        | string                      | Any note to be attached to the payment                              |
| expiry      | No        | \DateTimeInterface / string | The date you want the payment to expire (Check `DateValue` details) |
| constraints | No        | array                       | Constraints for the payment (Check `Constraints` details)           |

DateValue:

A date value can be either any object implementing \DateTimeInterface or an ISO8601 string.

Constraints:

| Parameter           | Mandatory | Type    | Description                                               |
| ------------------- | --------- | ------- | --------------------------------------------------------- |
| minimum_card_amount | No        | integer | Minimum amount that must be processed with a card payment |

Returns:

This method returns the payment URL that the user must follow to complete the payment.
