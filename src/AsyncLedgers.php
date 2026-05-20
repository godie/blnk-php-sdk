<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncLedgers — Promise-returning ledger operations.
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncLedgers
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new ledger (async).
     *
     * @param  string       $name     Ledger name (required).
     * @param  array        $metaData Optional metadata key-value pairs.
     * @return PromiseInterface<array> The created ledger.
     */
    public function create(string $name, array $metaData = []): PromiseInterface
    {
        return $this->client->postAsync('/ledgers', [
            'name'      => $name,
            'meta_data' => $metaData,
        ]);
    }

    /**
     * Get a ledger by its ID (async).
     *
     * @param  string $id Ledger ID (e.g., "ldg_...").
     * @return PromiseInterface<array> The ledger.
     */
    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/ledgers/{$id}");
    }

    /**
     * List all ledgers with optional pagination (async).
     *
     * @param  int    $limit  Number of records (default 10).
     * @param  int    $offset Offset for pagination (default 0).
     * @return PromiseInterface<array> Array of ledgers.
     */
    public function all(int $limit = 10, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/ledgers', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter ledgers using advanced query-parameter filters (async).
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Offset for pagination.
     * @return PromiseInterface<array> Array of ledgers.
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/ledgers', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter ledgers via JSON body (POST /ledgers/filter) (async).
     *
     * @param  array $payload Filter payload as described in the API docs.
     * @return PromiseInterface<array> Filtered results (may include 'total_count').
     */
    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/ledgers/filter', $payload);
    }

    /**
     * Update a ledger's name (async).
     *
     * @param  string $id   Ledger ID.
     * @param  string $name New name.
     * @return PromiseInterface<array> Updated ledger.
     */
    public function update(string $id, string $name): PromiseInterface
    {
        return $this->client->putAsync("/ledgers/{$id}", ['name' => $name]);
    }
}
