<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Search — Full-text search and reindexing via Typesense.
 *
 * Endpoints covered:
 *   POST   /search/:collection   Search within a collection
 *   POST   /multi-search          Multi-collection search
 *   POST   /search/reindex         Start a reindex operation
 *   GET    /search/reindex         Get reindex progress
 */
class Search
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Search within a specific collection.
     *
     * @param  string $collection Collection name (e.g., "transactions", "ledgers", "balances", "identities").
     * @param  array  $query      Typesense search parameters (e.g., ['q' => 'USD', 'query_by' => 'currency']).
     * @return array              Search results.
     * @throws BlnkException
     */
    public function search(string $collection, array $query): array
    {
        return $this->client->post("/search/{$collection}", $query);
    }

    /**
     * Perform a multi-collection search.
     *
     * @param  array $searches Array of search requests, each with collection and search params.
     * @return array           Multi-search results.
     * @throws BlnkException
     */
    public function multiSearch(array $searches): array
    {
        return $this->client->post('/multi-search', ['searches' => $searches]);
    }

    /**
     * Start reindexing all data from the database into Typesense.
     *
     * @param  int   $batchSize Batch size for processing (default 1000).
     * @return array            Progress and status message.
     * @throws BlnkException
     */
    public function startReindex(int $batchSize = 1000): array
    {
        return $this->client->post('/search/reindex', ['batch_size' => $batchSize]);
    }

    /**
     * Get the current progress of the reindex operation.
     *
     * @return array Progress details.
     * @throws BlnkException
     */
    public function getReindexProgress(): array
    {
        return $this->client->get('/search/reindex');
    }
}
