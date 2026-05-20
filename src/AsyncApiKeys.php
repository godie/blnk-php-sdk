<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncApiKeys — Promise-returning API key operations.
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncApiKeys
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new API key (async).
     *
     * @param  string $name      Friendly name for the key.
     * @param  string $owner     Owner identifier.
     * @param  array  $scopes    List of permission scopes (e.g., ["ledgers:read", "transactions:write"]).
     * @param  string $expiresAt ISO 8601 expiration timestamp.
     * @return PromiseInterface<array> The created API key (includes the full key value).
     */
    public function create(string $name, string $owner, array $scopes, string $expiresAt): PromiseInterface
    {
        return $this->client->postAsync('/api-keys', [
            'name'       => $name,
            'owner'      => $owner,
            'scopes'     => $scopes,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * List all API keys for an owner (async).
     *
     * @param  string $owner Owner identifier.
     * @return PromiseInterface<array> Array of API key objects.
     */
    public function all(string $owner): PromiseInterface
    {
        return $this->client->getAsync('/api-keys', ['owner' => $owner]);
    }

    /**
     * Revoke an API key (async).
     *
     * @param  string $id    API key ID.
     * @param  string $owner Owner identifier (for authorization).
     * @return PromiseInterface<array> Empty on success (204 No Content).
     */
    public function revoke(string $id, string $owner): PromiseInterface
    {
        return $this->client->deleteAsync("/api-keys/{$id}", ['owner' => $owner]);
    }
}
