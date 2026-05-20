<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Hooks — Manage webhooks for pre/post transaction processing.
 *
 * Endpoints covered:
 *   POST   /hooks       Register a webhook
 *   PUT    /hooks/:id   Update a webhook
 *   GET    /hooks/:id   Get a webhook by ID
 *   GET    /hooks       List hooks (filterable by ?type=)
 *   DELETE /hooks/:id   Delete a hook
 */
class Hooks
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Register a new webhook.
     *
     * @param  array $data {
     *     Required: name, url, type (PRE_TRANSACTION or POST_TRANSACTION)
     *     Optional: active, timeout, retry_count
     * }
     * @return array The created hook.
     * @throws BlnkException
     */
    public function create(array $data): array
    {
        return $this->client->post('/hooks', $data);
    }

    /**
     * Get a hook by its ID.
     *
     * @param  string $id Hook ID.
     * @return array      The hook.
     * @throws BlnkException
     */
    public function get(string $id): array
    {
        return $this->client->get("/hooks/{$id}");
    }

    /**
     * Update an existing hook.
     *
     * @param  string $id   Hook ID.
     * @param  array  $data Updated hook fields.
     * @return array        The updated hook.
     * @throws BlnkException
     */
    public function update(string $id, array $data): array
    {
        return $this->client->put("/hooks/{$id}", $data);
    }

    /**
     * List all hooks, optionally filtered by type.
     *
     * @param  string|null $type Hook type filter: "PRE_TRANSACTION" or "POST_TRANSACTION" (null = all).
     * @return array             Array of hooks.
     * @throws BlnkException
     */
    public function all(?string $type = null): array
    {
        $query = $type ? ['type' => $type] : [];
        return $this->client->get('/hooks', $query);
    }

    /**
     * Delete a hook.
     *
     * @param  string $id Hook ID.
     * @return array      Confirmation message.
     * @throws BlnkException
     */
    public function delete(string $id): array
    {
        return $this->client->delete("/hooks/{$id}");
    }
}
