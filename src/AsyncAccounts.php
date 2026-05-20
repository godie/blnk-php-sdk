<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncAccounts — Promise-returning account operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncAccounts
{
    public function __construct(private BlnkClient $client) {}

    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/accounts', $data);
    }

    public function get(string $id, array $includes = []): PromiseInterface
    {
        $query = $includes ? ['include' => $includes] : [];
        return $this->client->getAsync("/accounts/{$id}", $query);
    }

    public function all(int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/accounts', ['limit' => $limit, 'offset' => $offset]);
    }

    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/accounts', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/accounts/filter', $payload);
    }

    public function mock(): PromiseInterface
    {
        return $this->client->getAsync('/mocked-account');
    }
}
