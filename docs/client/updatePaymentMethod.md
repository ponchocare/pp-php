# updatePaymentMethod

This method allows to change the Payment Method properties.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$client->updatePaymentMethod($paymentMethodId, $update);
```

## Usage

Declaration:

```php
$client->updatePaymentMethod(string $paymentMethodId, array $update): void;
```

Parameters:

| Parameter       | Description                                                               |
| --------------- | ------------------------------------------------------------------------- |
| paymentMethodId | Payment method ID. You can find this in the callbacks' body               |
| update          | Parameters for the update to happen (Check `PaymentMethodUpdate` details) |

PaymentMethodUpdate:

| Parameter        | Mandatory | Type    | Description                                                                                              |
| ---------------- | --------- | ------- | -------------------------------------------------------------------------------------------------------- |
| urn              | Yes       | string  | The location Unique Reference Number                                                                     |
| email            | Yes       | string  | Email authoring the update (doesn't need to be a signed in user)                                         |
| type             | Yes       | string  | The new payment method type. It can be `card`, `childcare-voucher`, or `tax-free-childcare`              |
| amount           | Yes       | integer | The new payment method amount                                                                            |
| voucher_provider | Maybe     | string  | If type is `childcare-voucher`, this must contain the voucher provider (Check `VoucherProvider` details) |

VoucherProvider:

This will be the voucher provider's name after applying the following rules:
- The name will be completely in lowercase.
- Any symbol will be removed (this includes anything except letters, numbers, and spaces).
- Any space will be replaced with underscore (`_`).
- Multiple consecutive underscores will be collapsed into a single one.

For example, the hypothetical voucher provider `Co-operating & Helping 4ever` will be sent as `cooperating_helping_4ever`.

Returns:

This method doesn't return a usable value.
