<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncSearch — Promise-returning search operations.
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncSearch
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Search within a specific collection (async).
     *
     * @param  string $collection Collection name (e.g., "transactions", "ledgers", "balances", "identities").
     * @param  array  $query      Typesense search parameters (e.g., ['q' => 'USD', 'query_by' => 'currency']).
     * @return PromiseInterface<array> Search results.
     */
    public function search(string $collection, array $query): PromiseInterface
    {
        return $this->client->postAsync("/search/{$collection}", $query);
    }

    /**
     * Perform a multi-collection search (async).
     *
     * @param  array $searches Array of search requests, each with collection and search params.
     * @return PromiseInterface<array> Multi-search results.
     */
    public function multiSearch(array $searches): PromiseInterface
    {
        return $this->client->postAsync('/multi-search', ['searches' => $searches]);
    }

    /**
     * Start reindexing all data from the database into Typesense (async).
     *
     * @param  int   $batchSize Batch size for processing (default 1000).
     * @return PromiseInterface<array> Progress and status message.
     */
    public function startReindex(int $batchSize = 1000): PromiseInterface
    {
        return $this->client->postAsync('/search/reindex', ['batch_size' => $batchSize]);
    }

    /**
     * Get the current progress of the reindex operation (async).
     *
     * @return PromiseInterface<array> Progress details.
     */
    public function getReindexProgress(): PromiseInterface
    {
        return $this->client->getAsync('/search/reindex');
    }
}
