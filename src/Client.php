<?php

namespace PonchoPay;

use function PonchoPay\Utils\replaceParams;
use function PonchoPay\Utils\serialise;

/**
 * PonchoPay client.
 * It collects the methods to manipulate payments in PonchoPay.
 */
final class Client
{
    private Api $api;
    private string $key;

    public function __construct(string $key, string $base = 'https://pay.ponchopay.com/')
    {
        $this->api = new Api($base);
        $this->key = $key;
    }

    /**
     * Validates whether a location is able to process payments.
     *
     * @param array{urn: string, email: string} $payload
     *
     * @return object{
     *   verification_status: bool,
     *   card_payments_enabled: bool,
     *   childcare_voucher_payments_enabled: bool,
     *   tax_free_childcare_payments_enabled: bool
     * }
     */
    public function validateLocationUrn(array $payload): object
    {
        return $this->issueGetRequest('/api/integration/validate', $payload);
    }

    /**
     * Initiates a payment.
     * This method returns a URL to redirect the user to.
     *
     * @param array{
     *    metadata: string,
     *    urn: string,
     *    amount: int,
     *    email: string,
     *    note?: string,
     *    expiry?: \DateTimeInterface|string,
     *    constraints?: array{
     *        minimum_card_amount?: int
     *    },
     *    line_items?: array{
     *        description: string,
     *        amount: int,
     *        quantity: int
     *    }[]
     * } $init
     */
    public function initiatePayment(array $init): string
    {
        return $this->getRedirectLocation('/api/integration/generic/initiate', $init);
    }

    /**
     * Initiates a subscription.
     * This method returns a URL to redirect the user to.
     *
     * @param array{
     *    urn: string,
     *    amount: int,
     *    metadata: string,
     *    note?: string,
     *    email: string,
     *    repetition:
     *        array{granularity: 'day', period: int} |
     *        array{granularity: 'week', period: int, weekdays: string[]} |
     *        array{granularity: 'month', period: int, day: int} |
     *        array{granularity: 'year', period: int},
     *    ending?:
     *        array{condition: 'never'} |
     *        array{condition: 'occurrences', occurrences: int} |
     *        array{condition: 'date', date: string | \DateTimeInterface},
     *    additional_one_time_payment?: array{
     *        metadata: string,
     *        amount: int,
     *        note?: string,
     *        expiry?: string | \DateTimeInterface,
     *        constraints?: array{
     *            minimum_card_amount?: int
     *       }
     *    }
     * } $init
     */
    public function initiateSubscription(array $init): string
    {
        return $this->getRedirectLocation('/api/integration/generic/subscription', $init);
    }

    /**
     * Updates a single payment method within a payment.
     *
     * @param array{
     *    urn: string,
     *    email: string,
     *    type: 'card'|'childcare-voucher'|'tax-free-childcare',
     *    amount: int,
     *    voucher_provider?: string
     * } $update
     */
    public function updatePaymentMethod(string $paymentMethodId, array $update): void
    {
        $path = replaceParams('/api/payment-method/[paymentMethodId]', ['paymentMethodId' => $paymentMethodId]);
        $this->issuePutRequest($path, $update);
    }

    /**
     * Refunds a single payment method within a payment.
     *
     * @param array{urn: string, email: string, amount: int} $refund
     */
    public function refundPaymentMethod(string $paymentMethodId, array $refund): void
    {
        $path = replaceParams('/api/payment-method/[paymentMethodId]/refund', ['paymentMethodId' => $paymentMethodId]);
        $this->issuePutRequest($path, $refund);
    }

    /**
     * Requests the cancelation of a payment.
     *
     * @param array{urn: string, email: string} $payload
     */
    public function cancelPayment(string $paymentId, array $payload): void
    {
        $path = replaceParams('/api/payment/[paymentId]/cancel', ['paymentId' => $paymentId]);
        $this->issuePutRequest($path, $payload);
    }

    /**
     * Requests the cancelation of a recursion.
     *
     * @param array{urn: string, email: string} $payload
     */
    public function cancelRecursion(string $recursionId, array $payload): void
    {
        $path = replaceParams('/api/recursion/[recursionId]/cancel', ['recursionId' => $recursionId]);
        $this->issuePutRequest($path, $payload);
    }

    private function getRedirectLocation(string $url, array $data): string
    {
        $body = serialise([
            ...$data,
            'token' => createToken($this->key, $data['metadata']),
        ]);

        $response = $this->api->makePostRequest($url, [], $body);
        if (302 === $response->getStatusCode()) {
            return $response->getHeaders(false)['location'][0];
        }

        throw new PonchoPayException("Unexpected response. Expected 302 as status code but {$response->getStatusCode()} was received");
    }

    private function issueGetRequest(string $url, array $data): object
    {
        $urn = $data['urn'];
        $email = $data['email'];

        $jwt = createJWT($urn, $this->key, $email, '');

        $headers = ['Authorization' => 'Bearer '.$jwt];
        $response = $this->api->makeGetRequest($url, $headers);

        if (200 !== $response->getStatusCode()) {
            throw new PonchoPayException("Unexpected response. Expected 200 as status code but {$response->getStatusCode()} was received");
        }

        $body = $response->getContent();


        $decoded = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        if (!\is_object($decoded)) {
            throw new PonchoPayException('Unexpected response body. Expected JSON object.');
        }

        return $decoded;
    }

    private function issuePutRequest(string $url, array $data): void
    {
        $urn = $data['urn'];
        $email = $data['email'];

        $body = serialise(array_diff_key($data, ['urn' => $urn, 'email' => $email]));
        $jwt = createJWT($urn, $this->key, $email, $body);

        $headers = ['Authorization' => 'Bearer '.$jwt];
        $response = $this->api->makePutRequest($url, $headers, $body);

        if (204 !== $response->getStatusCode()) {
            throw new PonchoPayException("Unexpected response. Expected 204 as status code but {$response->getStatusCode()} was received");
        }
    }
}
