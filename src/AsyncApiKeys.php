<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncApiKeys — Promise-returning API key operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncApiKeys
{
    public function __construct(private BlnkClient $client) {}

    public function create(string $name, string $owner, array $scopes, string $expiresAt): PromiseInterface
    {
        return $this->client->postAsync('/api-keys', [
            'name'       => $name,
            'owner'      => $owner,
            'scopes'     => $scopes,
            'expires_at' => $expiresAt,
        ]);
    }

    public function all(string $owner): PromiseInterface
    {
        return $this->client->getAsync('/api-keys', ['owner' => $owner]);
    }

    public function revoke(string $id, string $owner): PromiseInterface
    {
        return $this->client->deleteAsync("/api-keys/{$id}", ['owner' => $owner]);
    }
}
