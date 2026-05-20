<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncSearch — Promise-returning search operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncSearch
{
    public function __construct(private BlnkClient $client) {}

    public function search(string $collection, array $query): PromiseInterface
    {
        return $this->client->postAsync("/search/{$collection}", $query);
    }

    public function multiSearch(array $searches): PromiseInterface
    {
        return $this->client->postAsync('/multi-search', ['searches' => $searches]);
    }

    public function startReindex(int $batchSize = 1000): PromiseInterface
    {
        return $this->client->postAsync('/search/reindex', ['batch_size' => $batchSize]);
    }

    public function getReindexProgress(): PromiseInterface
    {
        return $this->client->getAsync('/search/reindex');
    }
}
