<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncLedgers — Promise-returning ledger operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncLedgers
{
    public function __construct(private BlnkClient $client) {}

    public function create(string $name, array $metaData = []): PromiseInterface
    {
        return $this->client->postAsync('/ledgers', [
            'name'      => $name,
            'meta_data' => $metaData,
        ]);
    }

    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/ledgers/{$id}");
    }

    public function all(int $limit = 10, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/ledgers', ['limit' => $limit, 'offset' => $offset]);
    }

    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/ledgers', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/ledgers/filter', $payload);
    }

    public function update(string $id, string $name): PromiseInterface
    {
        return $this->client->putAsync("/ledgers/{$id}", ['name' => $name]);
    }
}
