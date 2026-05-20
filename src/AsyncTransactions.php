<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncTransactions — Promise-returning transaction operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncTransactions
{
    public function __construct(private BlnkClient $client) {}

    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/transactions', $data);
    }

    public function createBulk(array $data): PromiseInterface
    {
        return $this->client->postAsync('/transactions/bulk', $data);
    }

    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/transactions/{$id}");
    }

    public function getByReference(string $reference): PromiseInterface
    {
        return $this->client->getAsync("/transactions/reference/{$reference}");
    }

    public function all(int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/transactions', ['limit' => $limit, 'offset' => $offset]);
    }

    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/transactions', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/transactions/filter', $payload);
    }

    public function refund(string $id): PromiseInterface
    {
        return $this->client->postAsync("/refund-transaction/{$id}");
    }

    public function updateInflightStatus(string $txId, string $status, float $amount = 0): PromiseInterface
    {
        $body = ['status' => $status];
        if ($amount > 0) {
            $body['amount'] = $amount;
        }
        return $this->client->putAsync("/transactions/inflight/{$txId}", $body);
    }

    public function lineage(string $id): PromiseInterface
    {
        return $this->client->getAsync("/transactions/{$id}/lineage");
    }

    public function recoverQueued(string $threshold = '2m'): PromiseInterface
    {
        return $this->client->postAsync('/transactions/recover', [], ['threshold' => $threshold]);
    }
}
