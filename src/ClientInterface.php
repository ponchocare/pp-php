<?php

declare(strict_types=1);

namespace PonchoPay;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

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
interface ClientInterface
{
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
    public function validateLocationUrn(array $payload): object;

    /**
     * Initiates the payment transaction process for a user.
     *
     * @param PaymentInit $init a comprehensive associative array containing all necessary payment details:
     *
     * @return string the redirect URL that the user's browser must navigate to in order to complete the payment
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function initiatePayment(array $init): string;

    /**
     * Initiates the process for setting up a recurring subscription payment.
     *
     * @param SubscriptionInit $init a comprehensive array containing all necessary details for defining the subscription:
     *
     * @return string the redirect URL that the user's browser must navigate to in order to setup the subscription
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function initiateSubscription(array $init): string;

    /**
     * Updates specific details for an existing payment method associated with a payment record.
     *
     * @param string              $paymentMethodId the identifier of the payment method to be modified
     * @param PaymentMethodUpdate $update          an associative array containing the updated payment details
     *
     * @throw TransportExceptionInterface if the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function updatePaymentMethod(string $paymentMethodId, array $update): void;

    /**
     * Refunds a single payment method within a payment.
     *
     * @param string              $paymentMethodId the identifier of the payment method to be refunded
     * @param PaymentMethodRefund $refund          the refund payload
     */
    public function refundPaymentMethod(string $paymentMethodId, array $refund): void;

    /**
     * Requests the cancelation of a payment.
     *
     * @param string     $paymentId the identifier of the payment method to be cancelled
     * @param JWTPayload $payload   the cancellation payload
     */
    public function cancelPayment(string $paymentId, array $payload): void;

    /**
     * Requests the cancelation of a recursion.
     *
     * @param string     $recursionId the unique identifier of the recursion to be cancelled
     * @param JWTPayload $payload     the cancellation payload
     */
    public function cancelRecursion(string $recursionId, array $payload): void;

    /**
     * Checks if the provided data is a valid callback signature by comparing it against a computed HMAC hash.
     *
     * @param string                 $signature the expected SHA256 HMAC signature string
     * @param StreamInterface|string $payload   the data received in the callback. This can be a `StreamInterface`
     *                                          object or a raw string.
     *
     * @return bool returns `true` if the signature matches the generated hash, `false` otherwise
     */
    public function isValidCallback(string $signature, StreamInterface|string $payload): bool;

    /**
     * Validates an incoming HTTP request by checking its security signature against the configured API key.
     *
     * @param RequestInterface $request the incoming PSR-7 request object containing the signature in the header and the body
     *
     * @return bool returns `true` if the request signature is valid, otherwise it returns `false`
     *
     * @throw PonchoPayException if the signature header is missing or if the body content is invalid
     */
    public function isValidRequest(RequestInterface $request): bool;

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
    public function parseCallback(string $signature, StreamInterface|string $payload): object;

    /**
     * Parses an incoming HTTP request object by extracting the signature from the headers and validating the body.
     *
     * @param RequestInterface $request the incoming PSR-7 request object
     *
     * @return PaymentWebhook the fully parsed and validated payload object from the request
     *
     * @throw PonchoPayException if the signature header is missing or if the body content is invalid.
     */
    public function parseRequest(RequestInterface $request): object;
}
