<?php

require __DIR__.'/vendor/autoload.php';

use PonchoPay\Client;

/**
 * This file provides an example on how to use `pp-php` to interact with PonchoPay.
 *
 * This example initiates a payment, waits for any input and cancels the payment.
 */

/**
 * Replace the following values with the values relevant to your account.
 *
 * You can find those values by browsing to either of the following URLs depending on the environment:
 * - Demo: https://demo.ponchopay.com/provider/admin/settings/api-integration
 * - Production: https://pay.ponchopay.com/provider/admin/settings/api-integration
 */
// If you don't pass this variable, the production environment will be used.
$base = 'https://demo.ponchopay.com/';

// This is the integration key assigned to you.
$key = '🤫';

// This is the location Unique Reference Number.
$urn = '🏠';

// This is the payment amount in pence. We will create a payment for £20.34.
$amount = 2034;

// This is your customer's email. They are expected to pay for this payment.
$email = 'tommy@server.com';

// This is any piece of information that you want to attach to the payment.
$metadata = json_encode(['order' => rand(1, 1000)]);

$client = new Client($key, $base);

/**
 * Let's validate the location first
 */
$validation = $client->validateLocationUrn(['urn' => $urn, 'email' => 'validate@author.com']);

echo 'The location is'.($validation->verification_status ? ' ' : ' not ')."verified\n";
echo 'The location can'.($validation->card_payments_enabled ? ' ' : ' not ')."process card/bank payments\n";
echo 'The location can'.($validation->childcare_voucher_payments_enabled ? ' ' : ' not ')."process childcare voucher payments\n";
echo 'The location can'.($validation->tax_free_childcare_payments_enabled ? ' ' : ' not ')."process tax-free childcare payments\n";

if (!$validation->verification_status) {
    echo "Unfortunately, the location is not ready to process payments.\n";
    echo "But don't worry, you can always let us know at help@ponchopay.com\n";

    exit(1);
}

/**
 * Let's create a payment!
 */
$payment = $client->initiatePayment([
    'amount' => $amount,
    'metadata' => $metadata,
    'urn' => $urn,
    'email' => $email,
]);

echo "\n";
echo "############################################################\n";
echo "A payment has been generated. Please, go here to pay for it:\n";
echo $payment."\n";
echo "############################################################\n";
echo "\n";

// Give some time to interact with the payment.
echo "Press [ESC] to quit. Press any key to cancel the payment\n";
system('stty -icanon');
$char = fread(STDIN, 1);
if (27 === ord($char)) {
    echo "Quitting. Bye!\n";

    exit(0);
}

// Now, let's cancel the payment we just created, just for fun!
preg_match('/([^\/]+)$/', $payment, $matches);
$paymentId = $matches[1];
$client->cancelPayment($paymentId, ['urn' => $urn, 'email' => 'cancel@author.com']);

echo "\n";
echo "The payment has been successfully canceled!\n";
