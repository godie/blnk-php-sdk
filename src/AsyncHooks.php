<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncHooks — Promise-returning webhook operations.
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncHooks
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Register a new webhook (async).
     *
     * @param  array $data {
     *     Required: name, url, type (PRE_TRANSACTION or POST_TRANSACTION)
     *     Optional: active, timeout, retry_count
     * }
     * @return PromiseInterface<array> The created hook.
     */
    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/hooks', $data);
    }

    /**
     * Get a hook by its ID (async).
     *
     * @param  string $id Hook ID.
     * @return PromiseInterface<array> The hook.
     */
    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/hooks/{$id}");
    }

    /**
     * Update an existing hook (async).
     *
     * @param  string $id   Hook ID.
     * @param  array  $data Updated hook fields.
     * @return PromiseInterface<array> The updated hook.
     */
    public function update(string $id, array $data): PromiseInterface
    {
        return $this->client->putAsync("/hooks/{$id}", $data);
    }

    /**
     * List all hooks, optionally filtered by type (async).
     *
     * @param  string|null $type Hook type filter: "PRE_TRANSACTION" or "POST_TRANSACTION" (null = all).
     * @return PromiseInterface<array> Array of hooks.
     */
    public function all(?string $type = null): PromiseInterface
    {
        $query = $type ? ['type' => $type] : [];
        return $this->client->getAsync('/hooks', $query);
    }

    /**
     * Delete a hook (async).
     *
     * @param  string $id Hook ID.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function delete(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/hooks/{$id}");
    }
}
