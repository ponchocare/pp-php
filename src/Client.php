<?php

declare(strict_types=1);

namespace PonchoPay;

use PonchoPay\Utils\Binary;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

use function PonchoPay\Utils\deserialise;
use function PonchoPay\Utils\replaceParams;
use function PonchoPay\Utils\serialise;
use function PonchoPay\Utils\sha256Hmac;

/**
 * Initializes a new instance of the Client. This sets up the core API connection
 * using the provided API key and determines the API client instance based on
 * whether an existing Api object or a base URL string is provided.
 *
 * @phpstan-import-type LocationStatus from Types
 * @phpstan-import-type PaymentInit from Types
 * @phpstan-import-type SubscriptionInit from Types
 * @phpstan-import-type JWTPayload from Types
 * @phpstan-import-type PaymentMethodRefund from Types
 * @phpstan-import-type PaymentMethodUpdate from Types
 * @phpstan-import-type PaymentWebhook from Types
 */
final class Client implements ClientInterface
{
    private readonly Api $api;
    private readonly string $key;

    /**
     * @param string     $key  the integration key found in the https://pay.ponchopay.com/provider/admin/settings/api-integration page
     * @param Api|string $base if an existing Api object is passed, it will be used;
     *                         otherwise, the base URL string is used to instantiate a new Api object
     */
    public function __construct(string $key, Api|string $base = 'https://pay.ponchopay.com/')
    {
        $this->api = $base instanceof Api ? $base : new Api($base);
        $this->key = $key;
    }

    /**
     * Validates the payment processing capabilities for a specified location URN.
     * This function is used to check which payment methods are available at a given location.
     *
     * @param JWTPayload $payload an associative array containing the 'urn' (Unique Resource Name) and 'email' required for location identification
     *
     * @return LocationStatus an object structure detailing payment enablement flags as boolean values
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function validateLocationUrn(array $payload): object
    {
        return $this->issueGetRequest('/api/integration/validate', $payload);
    }

    /**
     * Initiates the payment transaction process for a user.
     *
     * @param PaymentInit $init a comprehensive associative array containing all necessary payment details:
     *
     * @return string the redirect URL that the user's browser must navigate to in order to complete the payment
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function initiatePayment(array $init): string
    {
        return $this->getRedirectLocation('/api/integration/generic/initiate', $init);
    }

    /**
     * Initiates the process for setting up a recurring subscription payment.
     *
     * @param SubscriptionInit $init a comprehensive array containing all necessary details for defining the subscription:
     *
     * @return string the redirect URL that the user's browser must navigate to in order to setup the subscription
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function initiateSubscription(array $init): string
    {
        return $this->getRedirectLocation('/api/integration/generic/subscription/redirect', $init);
    }

    /**
     * Updates specific details for an existing payment method associated with a payment record.
     *
     * @param string              $paymentMethodId the identifier of the payment method to be modified
     * @param PaymentMethodUpdate $update          an associative array containing the updated payment details
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function updatePaymentMethod(string $paymentMethodId, array $update): void
    {
        $path = replaceParams('/api/payment-method/[paymentMethodId]', ['paymentMethodId' => $paymentMethodId]);
        $this->issuePutRequest($path, $update);
    }

    /**
     * Refunds a single payment method within a payment.
     *
     * @param string              $paymentMethodId the identifier of the payment method to be refunded
     * @param PaymentMethodRefund $refund          the refund payload
     */
    public function refundPaymentMethod(string $paymentMethodId, array $refund): void
    {
        $path = replaceParams('/api/payment-method/[paymentMethodId]/refund', ['paymentMethodId' => $paymentMethodId]);
        $this->issuePutRequest($path, $refund);
    }

    /**
     * Requests the cancelation of a payment.
     *
     * @param string     $paymentId the identifier of the payment method to be cancelled
     * @param JWTPayload $payload   the cancellation payload
     */
    public function cancelPayment(string $paymentId, array $payload): void
    {
        $path = replaceParams('/api/payment/[paymentId]/cancel', ['paymentId' => $paymentId]);
        $this->issuePutRequest($path, $payload);
    }

    /**
     * Requests the cancelation of a recursion.
     *
     * @param string     $recursionId the unique identifier of the recursion to be cancelled
     * @param JWTPayload $payload     the cancellation payload
     */
    public function cancelRecursion(string $recursionId, array $payload): void
    {
        $path = replaceParams('/api/recursion/[recursionId]/cancel', ['recursionId' => $recursionId]);
        $this->issuePutRequest($path, $payload);
    }

    /**
     * Checks if the provided data is a valid callback signature by comparing it against a computed HMAC hash.
     *
     * @param string                 $signature the expected SHA256 HMAC signature string
     * @param StreamInterface|string $payload   the data received in the callback. This can be a `StreamInterface`
     *                                          object or a raw string.
     *
     * @return bool returns `true` if the signature matches the generated hash, `false` otherwise
     */
    public function isValidCallback(string $signature, StreamInterface|string $payload): bool
    {
        $data = $this->getRawData($payload);

        return hash_equals($signature, sha256Hmac($this->key, $data));
    }

    /**
     * Validates an incoming HTTP request by checking its security signature against the configured API key.
     *
     * @param RequestInterface $request the incoming PSR-7 request object containing the signature in the header and the body
     *
     * @return bool returns `true` if the request signature is valid, otherwise it returns `false`
     *
     * @throw PonchoPayException if the signature header is missing or if the body content is invalid
     */
    public function isValidRequest(RequestInterface $request): bool
    {
        return $this->isValidCallback($this->getSignature($request), $request->getBody());
    }

    /**
     * Decodes and validates a payment gateway callback payload received.
     *
     * @param string                 $signature the expected SHA256 HMAC signature provided in the request headers
     * @param StreamInterface|string $payload   The body content of the incoming callback request. This can be a `StreamInterface`
     *                                          object or a raw string.
     *
     * @return PaymentWebhook the fully parsed and validated payload object from the callback
     *
     * @throw PonchoPayException if the signature does not match, the body is not valid JSON, or any other processing error occurs.
     */
    public function parseCallback(string $signature, StreamInterface|string $payload): object
    {
        $data = $this->getRawData($payload);
        $isValid = $this->isValidCallback($signature, $data);
        if (!$isValid) {
            throw new PonchoPayException('Callback request failed the security signature');
        }

        return deserialise($data);
    }

    /**
     * Parses an incoming HTTP request object by extracting the signature from the headers and validating the body.
     *
     * @param RequestInterface $request the incoming PSR-7 request object
     *
     * @return PaymentWebhook the fully parsed and validated payload object from the request
     *
     * @throw PonchoPayException if the signature header is missing or if the body content is invalid.
     */
    public function parseRequest(RequestInterface $request): object
    {
        return $this->parseCallback($this->getSignature($request), $request->getBody());
    }

    /**
     * Extracts the security signature from the incoming PSR-7 Request object.
     * This method ensures that exactly one 'signature' header is present and returns its value.
     *
     * @param RequestInterface $request the incoming request containing the signature in its headers
     *
     * @return string the raw signature string obtained from the request header
     *
     * @throw PonchoPayException if the signature header is missing or if multiple signature headers are present.
     */
    private function getSignature(RequestInterface $request): string
    {
        $headers = $request->getHeader('signature');
        if (0 === count($headers)) {
            throw new PonchoPayException('Signature header not found');
        }
        if (count($headers) > 1) {
            throw new PonchoPayException('Multiple signature headers found');
        }

        return $headers[0];
    }

    /**
     * Reads the raw content from a StreamInterface request body, ensuring it does not exceed a defined limit.
     *
     * @param StreamInterface|string $payload the incoming stream object from the request or string data
     *
     * @return string the raw, readable string content from the body
     *
     * @throw PonchoPayException if the stream is unexpectedly long or if reading the stream fails.
     */
    private function getRawData(StreamInterface|string $payload): string
    {
        if ($payload instanceof StreamInterface) {
            // 2 MiB should be enough for standard PonchoPay interactions.
            // read() may return fewer bytes than requested without reaching EOF.
            $limit = Binary::fromMebibytes(2)->toBytes();
            $data = '';

            while (!$payload->eof() && strlen($data) <= $limit) {
                $chunk = $payload->read($limit + 1 - strlen($data));
                if ('' === $chunk) {
                    break;
                }
                $data .= $chunk;
            }

            if (strlen($data) > $limit) {
                throw new PonchoPayException('Stream too long. Expected a maximum of 2 MiB');
            }

            if ($payload->isSeekable()) {
                $payload->rewind();
            }

            return $data;
        }

        return $payload;
    }

    /**
     * It consumes endpoints returning redirection URLs. This method extracts the returned redirection.
     *
     * @param string                 $url  the API endpoint URL to initiate the redirect process
     * @param array<array-key,mixed> $data the array of initial payment data to be sent
     *
     * @return string the final redirect URL obtained from the API response headers
     *
     * @throws PonchoPayException if the API response status code is not a 302 redirect or if the request fails
     */
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

    /**
     * Executes an HTTP GET request to the API to retrieve and validate location-specific payment information.
     *
     * @param string              $url  the API endpoint URL to initiate the redirect process
     * @param array<string,mixed> $data the data for the location and the authorising email
     *
     * @return object the decoded JSON object containing the payment validation status
     *
     * @throws PonchoPayException          if the API response status code is not 200 or if the response body is not a valid JSON object
     * @throws TransportExceptionInterface If the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
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

        return deserialise($response->getContent());
    }

    /**
     * Executes an HTTP PUT request to the API to modify or update a payment record.
     *
     * @param string                 $url  the API endpoint URL to initiate the redirect process
     * @param array<array-key,mixed> $data the data for the location, the authorising email, and the endpoint payload
     *
     * @throws PonchoPayException          if the API response status code is not 204 No Content or if the request fails
     * @throws TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
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
