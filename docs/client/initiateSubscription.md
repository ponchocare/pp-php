# initiateSubscription

This method allows to initialise a subscription in PonchoPay.

## How to

Import the client and call the method by passing the relevant parameters:

```php
use PonchoPay\Client;

$client = new Client($key);
$payment = $client->initiateSubscription($init);
```

## Usage

Declaration:

```php
$client->initiateSubscription(array $init): string;
```

Parameters:

| Parameter | Description                                                      |
| --------- | ---------------------------------------------------------------- |
| init      | Payment initialisation object (Check `SubscriptionInit` details) |

SubscriptionInit:

| Parameter                   | Mandatory | Type    | Description                                                          |
| --------------------------- | --------- | ------- | -------------------------------------------------------------------- |
| urn                         | Yes       | string  | The location Unique Reference Number                                 |
| amount                      | Yes       | integer | The payable amount in pences for each generated payment              |
| metadata                    | Yes       | string  | Any string you want to keep attached to the subscription             |
| email                       | Yes       | string  | The user email                                                       |
| repetition                  | Yes       | array   | The frequency to generate payments (Check `Repetition` details)      |
| ending                      | No        | array   | The condition to terminate the subscription (Check `Ending` details) |
| note                        | No        | string  | Any note to be attached to the subscription                          |
| additional_one_time_payment | No        | array   | An additional one time payment (Check `OneTimePayment` details)      |

Repetition:

| Parameter   | Mandatory | Type    | Description                                                                     |
| ----------- | --------- | ------- | ------------------------------------------------------------------------------- |
| granularity | Yes       | string  | The time span range. It can be `day`, `week`, `month`, or `year`                |
| period      | Yes       | integer | The amount of granularity before the next payment is generated.                 |
| weekdays    | Maybe     | array   | If granularity is `week`, this must contain an array with the days in lowercase |
| day         | Maybe     | integer | If granularity is `month`, this must contain the day of the month               |

Ending:

| Parameter   | Mandatory | Type                        | Description                                                                          |
| ----------- | --------- | --------------------------- | ------------------------------------------------------------------------------------ |
| condition   | Yes       | string                      | The termination condition. It can be `never`, `occurrences`, or `date`               |
| occurrences | Maybe     | integer                     | If condition is `occurrences`, this must contain the number of payments to terminate |
| date        | Maybe     | \DateTimeInterface / string | If condition is `date`, this must contain the date (Check `DateValue` details)       |

OneTimePayment:

| Parameter   | Mandatory | Type                        | Description                                                         |
| ----------- | --------- | --------------------------- | ------------------------------------------------------------------- |
| metadata    | Yes       | string                      | Any string you want to keep attached to the payment                 |
| amount      | Yes       | integer                     | The payable amount in pences                                        |
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

This method returns the subscription URL that the user must follow to set up the subscription.
