<?php

declare(strict_types=1);

namespace Blnk;

/**
 * ApiKeys — Manage API keys for the Blnk instance.
 *
 * Endpoints covered:
 *   POST   /api-keys       Create an API key
 *   GET    /api-keys       List API keys
 *   DELETE /api-keys/:id   Revoke an API key
 */
class ApiKeys
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new API key.
     *
     * @param  string $name      Friendly name for the key.
     * @param  string $owner     Owner identifier.
     * @param  array  $scopes    List of permission scopes (e.g., ["ledgers:read", "transactions:write"]).
     * @param  string $expiresAt ISO 8601 expiration timestamp.
     * @return array             The created API key (includes the full key value).
     * @throws BlnkException
     */
    public function create(string $name, string $owner, array $scopes, string $expiresAt): array
    {
        return $this->client->post('/api-keys', [
            'name'       => $name,
            'owner'      => $owner,
            'scopes'     => $scopes,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * List all API keys for an owner.
     *
     * @param  string $owner Owner identifier.
     * @return array         Array of API key objects.
     * @throws BlnkException
     */
    public function all(string $owner): array
    {
        return $this->client->get('/api-keys', ['owner' => $owner]);
    }

    /**
     * Revoke an API key.
     *
     * @param  string $id    API key ID.
     * @param  string $owner Owner identifier (for authorization).
     * @return array         Empty on success (204 No Content).
     * @throws BlnkException
     */
    public function revoke(string $id, string $owner): array
    {
        return $this->client->delete("/api-keys/{$id}", ['owner' => $owner]);
    }
}
