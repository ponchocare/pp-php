<?php

declare(strict_types=1);

namespace PonchoPay;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function PonchoPay\Utils\joinPaths;
use function PonchoPay\Utils\telemetry;

/**
 * Creates a new API client instance, establishing a connection to the payment gateway
 * and configuring necessary default settings.
 */
/* final */ class Api
{
    private HttpClientInterface $client;
    private string $base;
    private array $headers;

    /**
     * @param string                $base    The base URL for the API endpoint
     * @param array<string, string> $headers Optional initial headers to apply to all requests
     */
    public function __construct(string $base, array $headers = [])
    {
        $this->client = HttpClient::create();
        $this->base = $base;
        $this->headers = $headers;
    }

    /**
     * Performs an HTTP GET request to a specified path on the API.
     *
     * @param string                $path    The relative path on the API endpoint to call
     * @param array<string, string> $headers Additional headers to include in the GET request
     *
     * @return ResponseInterface The response received from the API call
     *
     * @throws TransportExceptionInterface If the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function makeGetRequest(string $path, array $headers): ResponseInterface
    {
        return $this->makeRequest('GET', $path, $headers, '');
    }

    /**
     * Performs an HTTP POST request to a specified path on the API, sending data in the body.
     *
     * @param string                $path    The relative path on the API endpoint to call
     * @param array<string, string> $headers Additional headers to include in the POST request
     * @param string                $body    The payload data to be sent in the request body
     *
     * @return ResponseInterface The response received from the API call
     *
     * @throws TransportExceptionInterface If the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function makePostRequest(string $path, array $headers, string $body): ResponseInterface
    {
        return $this->makeRequest('POST', $path, $headers, $body);
    }

    /**
     * Performs an HTTP PUT request to a specified path on the API, updating resources with the given body.
     *
     * @param string                $path    The relative path on the API endpoint to call
     * @param array<string, string> $headers Additional headers to include in the PUT request
     * @param string                $body    The payload data used for updating the resource
     *
     * @return ResponseInterface The response received from the API call
     *
     * @throws TransportExceptionInterface If the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    public function makePutRequest(string $path, array $headers, string $body): ResponseInterface
    {
        return $this->makeRequest('PUT', $path, $headers, $body);
    }

    /**
     * Constructs the full, absolute URL for a given API path by combining it with the base URL.
     *
     * @param string $path The relative path segment
     *
     * @return string The fully qualified URL
     */
    private function getUrl(string $path): string
    {
        return joinPaths($this->base, $path);
    }

    /**
     * Merges the instance's base headers with required headers for a request, ensuring content type and telemetry are included.
     *
     * @return array<string, string> The complete set of headers to be used for the API request
     */
    private function getHeaders(): array
    {
        return array_merge($this->headers, [
            'content-type' => 'application/json',
            'x-telemetry' => json_encode(telemetry()),
        ]);
    }

    /**
     * Executes the core HTTP request logic using the configured HTTP client, handling method, path, headers, and body.
     *
     * @param string                $method  The HTTP method to use (e.g., 'GET', 'POST', 'PUT')
     * @param string                $path    The relative path on the API endpoint
     * @param array<string, string> $headers Headers specific to this request
     * @param string                $body    The body content for the request
     *
     * @return ResponseInterface The response object returned by the underlying HTTP client
     *
     * @throws TransportExceptionInterface If the underlying HTTP client fails to execute the request due to connectivity, timeouts, or server errors
     */
    private function makeRequest(string $method, string $path, array $headers, string $body): ResponseInterface
    {
        return $this->client->request($method, $this->getUrl($path), [
            'headers' => [...$headers, ...$this->getHeaders()],
            'body' => $body,
            'max_redirects' => 0,
        ]);
    }
}
