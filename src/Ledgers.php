<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Ledgers — Manage Blnk ledgers.
 *
 * Endpoints covered:
 *   POST   /ledgers              Create a ledger
 *   GET    /ledgers/:id          Get a ledger by ID
 *   GET    /ledgers              List all ledgers (optional ?limit=&offset=, and filters via query params)
 *   POST   /ledgers/filter       Filter ledgers via JSON body
 *   PUT    /ledgers/:id          Update a ledger name
 */
class Ledgers
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new ledger.
     *
     * @param  string       $name     Ledger name (required).
     * @param  array        $metaData Optional metadata key-value pairs.
     * @return array                  The created ledger.
     * @throws BlnkException
     */
    public function create(string $name, array $metaData = []): array
    {
        return $this->client->post('/ledgers', [
            'name'      => $name,
            'meta_data' => $metaData,
        ]);
    }

    /**
     * Get a ledger by its ID.
     *
     * @param  string $id Ledger ID (e.g., "ldg_...").
     * @return array      The ledger.
     * @throws BlnkException
     */
    public function get(string $id): array
    {
        return $this->client->get("/ledgers/{$id}");
    }

    /**
     * List all ledgers with optional pagination.
     *
     * @param  int    $limit  Number of records (default 10).
     * @param  int    $offset Offset for pagination (default 0).
     * @return array          Array of ledgers.
     * @throws BlnkException
     */
    public function all(int $limit = 10, int $offset = 0): array
    {
        return $this->client->get('/ledgers', [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Filter ledgers using advanced query-parameter filters.
     *
     * Example:
     *   $ledgers->filter(['name_eq' => 'USD Ledger', 'created_at_gte' => '2024-01-01']);
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Offset for pagination.
     * @return array          Array of ledgers.
     * @throws BlnkException
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/ledgers', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter ledgers via JSON body (POST /ledgers/filter).
     *
     * Example:
     *   $ledgers->filterWithBody([
     *       'filters' => [
     *           ['field' => 'name', 'operator' => 'ilike', 'value' => '%savings%']
     *       ],
     *       'limit' => 20,
     *       'offset' => 0,
     *       'include_count' => true,
     *   ]);
     *
     * @param  array $payload Filter payload as described in the API docs.
     * @return array          Filtered results (may include 'total_count' if include_count was true).
     * @throws BlnkException
     */
    public function filterWithBody(array $payload): array
    {
        return $this->client->post('/ledgers/filter', $payload);
    }

    /**
     * Update a ledger's name.
     *
     * @param  string $id   Ledger ID.
     * @param  string $name New name.
     * @return array        Updated ledger.
     * @throws BlnkException
     */
    public function update(string $id, string $name): array
    {
        return $this->client->put("/ledgers/{$id}", ['name' => $name]);
    }
}
