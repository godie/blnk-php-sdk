<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * BlnkClient — Main HTTP client for the Blnk Finance API.
 *
 * Authentication is done via the X-Blnk-Key header. Pass your API key
 * (or master secret key) when constructing the client.
 *
 * Usage (sync):
 *   $blnk = new BlnkClient('https://your-blnk-instance.com:5001', 'your-api-key');
 *   $ledgers = $blnk->ledgers->all();
 *
 * Usage (async):
 *   $async = $blnk->async();
 *   $promise = $async->ledgers->all(10, 0);
 *   $promise->then(function (array $ledgers) { ... });
 *
 *   // Or fire multiple requests concurrently:
 *   $results = Blnk\Promises::all([
 *       'ledgers'  => $async->ledgers->all(),
 *       'balances' => $async->balances->all(),
 *   ])->wait();
 */
class BlnkClient
{
    private Client $http;
    private string $baseUrl;
    private string $apiKey;

    public readonly Ledgers $ledgers;
    public readonly Balances $balances;
    public readonly Transactions $transactions;
    public readonly Identities $identities;
    public readonly Accounts $accounts;
    public readonly ApiKeys $apiKeys;
    public readonly Hooks $hooks;
    public readonly Reconciliation $reconciliation;
    public readonly Search $search;
    public readonly Metadata $metadata;
    public readonly Backup $backup;

    public function __construct(string $baseUrl, string $apiKey, array $clientOptions = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey  = $apiKey;

        $this->http = new Client(array_merge([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'X-Blnk-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'http_errors' => false,
        ], $clientOptions));

        $this->ledgers        = new Ledgers($this);
        $this->balances       = new Balances($this);
        $this->transactions   = new Transactions($this);
        $this->identities     = new Identities($this);
        $this->accounts       = new Accounts($this);
        $this->apiKeys        = new ApiKeys($this);
        $this->hooks          = new Hooks($this);
        $this->reconciliation = new Reconciliation($this);
        $this->search         = new Search($this);
        $this->metadata       = new Metadata($this);
        $this->backup         = new Backup($this);
    }

    // ─── HTTP helpers ───────────────────────────────────────────────────────

    /**
     * Send a GET request.
     *
     * @throws BlnkException
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    /**
     * Send a POST request.
     *
     * @throws BlnkException
     */
    public function post(string $path, array $body = [], array $query = []): array
    {
        return $this->request('POST', $path, ['json' => $body, 'query' => $query]);
    }

    /**
     * Send a PUT request.
     *
     * @throws BlnkException
     */
    public function put(string $path, array $body = [], array $query = []): array
    {
        return $this->request('PUT', $path, ['json' => $body, 'query' => $query]);
    }

    /**
     * Send a DELETE request.
     *
     * @throws BlnkException
     */
    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, ['query' => $query]);
    }

    /**
     * Get the async client with promise-returning resource methods.
     *
     * Usage:
     *   $async = $blnk->async();
     *   $async->ledgers->all()->then(fn($ledgers) => print_r($ledgers));
     */
    public function async(): AsyncBlnkClient
    {
        return new AsyncBlnkClient($this);
    }

    // ─── Async HTTP methods ─────────────────────────────────────────────────

    /**
     * Send an async GET request. Returns a promise that resolves to array.
     *
     * @return PromiseInterface
     */
    public function getAsync(string $path, array $query = []): PromiseInterface
    {
        return $this->requestAsync('GET', $path, ['query' => $query]);
    }

    /**
     * Send an async POST request. Returns a promise that resolves to array.
     *
     * @return PromiseInterface
     */
    public function postAsync(string $path, array $body = [], array $query = []): PromiseInterface
    {
        return $this->requestAsync('POST', $path, ['json' => $body, 'query' => $query]);
    }

    /**
     * Send an async PUT request. Returns a promise that resolves to array.
     *
     * @return PromiseInterface
     */
    public function putAsync(string $path, array $body = [], array $query = []): PromiseInterface
    {
        return $this->requestAsync('PUT', $path, ['json' => $body, 'query' => $query]);
    }

    /**
     * Send an async DELETE request. Returns a promise that resolves to array.
     *
     * @return PromiseInterface
     */
    public function deleteAsync(string $path, array $query = []): PromiseInterface
    {
        return $this->requestAsync('DELETE', $path, ['query' => $query]);
    }

    /**
     * Async file upload. Returns a promise that resolves to array.
     *
     * @return PromiseInterface
     */
    public function uploadFileAsync(string $path, string $filePath, array $extraFields = []): PromiseInterface
    {
        $multipart = [];

        foreach ($extraFields as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => $value];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            // Return a rejected promise
            return \GuzzleHttp\Promise\Create::rejectionFor(
                new BlnkException("Unable to open file: {$filePath}")
            );
        }

        $multipart[] = [
            'name'     => 'file',
            'contents' => $handle,
            'filename' => basename($filePath),
        ];

        return $this->http->requestAsync('POST', $path, ['multipart' => $multipart])
            ->then(
                function (ResponseInterface $response) use ($handle) {
                    fclose($handle);
                    return $this->decodeResponse($response);
                },
                function (\Throwable $e) use ($handle) {
                    fclose($handle);
                    throw new BlnkException($e->getMessage(), $e->getCode(), $e);
                }
            );
    }

    /**
     * Upload a file via multipart form data (used by reconciliation).
     *
     * @throws BlnkException
     */
    public function uploadFile(string $path, string $filePath, array $extraFields = []): array
    {
        $multipart = [];

        foreach ($extraFields as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => $value];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new BlnkException("Unable to open file: {$filePath}");
        }

        $multipart[] = [
            'name'     => 'file',
            'contents' => $handle,
            'filename' => basename($filePath),
        ];

        try {
            $response = $this->http->request('POST', $path, ['multipart' => $multipart]);
        } finally {
            fclose($handle);
        }

        return $this->decodeResponse($response);
    }

    /**
     * Low-level request dispatcher (sync).
     *
     * @throws BlnkException
     */
    public function request(string $method, string $path, array $options = []): array
    {
        try {
            $response = $this->http->request($method, $path, $options);
        } catch (RequestException $e) {
            $body = $e->hasResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();
            throw new BlnkException("Blnk API error: {$body}", $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new BlnkException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->decodeResponse($response);
    }

    /**
     * Low-level async request dispatcher. Returns a promise that resolves to array.
     *
     * @return PromiseInterface
     */
    public function requestAsync(string $method, string $path, array $options = []): PromiseInterface
    {
        return $this->http->requestAsync($method, $path, $options)
            ->then(
                function (ResponseInterface $response): array {
                    return $this->decodeResponse($response);
                },
                function (\Throwable $e): never {
                    if ($e instanceof RequestException && $e->hasResponse()) {
                        $body = (string) $e->getResponse()->getBody();
                        throw new BlnkException("Blnk API error: {$body}", $e->getCode(), $e);
                    }
                    throw new BlnkException($e->getMessage(), $e->getCode(), $e);
                }
            );
    }

    /**
     * Decode the JSON response, throwing on HTTP errors.
     *
     * @throws BlnkException
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body       = (string) $response->getBody();

        // Handle 204 No Content
        if ($statusCode === 204 || $body === '') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            if ($statusCode >= 400) {
                throw new BlnkException("Blnk API error (HTTP {$statusCode}): {$body}", $statusCode);
            }
            return ['data' => $body];
        }

        if ($statusCode >= 400) {
            $message = $decoded['error'] ?? $decoded['errors'] ?? $body;
            throw new BlnkException(
                is_array($message) ? json_encode($message) : (string) $message,
                $statusCode
            );
        }

        return $decoded;
    }
}
