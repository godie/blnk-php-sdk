<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncBalances — Promise-returning balance operations (includes monitors).
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncBalances
{
    public function __construct(private BlnkClient $client) {}

    // ─── Balance CRUD ──────────────────────────────────────────────────────

    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/balances', $data);
    }

    public function get(string $id, array $includes = [], bool $withQueued = false): PromiseInterface
    {
        $query = [];
        if ($includes) {
            $query['include'] = $includes;
        }
        if ($withQueued) {
            $query['with_queued'] = 'true';
        }
        return $this->client->getAsync("/balances/{$id}", $query);
    }

    public function all(int $limit = 10, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/balances', ['limit' => $limit, 'offset' => $offset]);
    }

    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/balances', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/balances/filter', $payload);
    }

    public function getByIndicator(string $indicator, string $currency): PromiseInterface
    {
        return $this->client->getAsync("/balances/indicator/{$indicator}/currency/{$currency}");
    }

    public function getAtTime(string $balanceId, ?string $timestamp = null, bool $fromSource = false): PromiseInterface
    {
        $query = [];
        if ($timestamp !== null) {
            $query['timestamp'] = $timestamp;
        }
        if ($fromSource) {
            $query['from_source'] = 'true';
        }
        return $this->client->getAsync("/balances/{$balanceId}/at", $query);
    }

    public function takeSnapshots(int $batchSize = 1000): PromiseInterface
    {
        return $this->client->postAsync('/balances-snapshots', [], ['batch_size' => $batchSize]);
    }

    public function updateIdentity(string $balanceId, string $identityId): PromiseInterface
    {
        return $this->client->putAsync("/balances/{$balanceId}/identity", ['identity_id' => $identityId]);
    }

    public function lineage(string $id): PromiseInterface
    {
        return $this->client->getAsync("/balances/{$id}/lineage");
    }

    // ─── Balance Monitors ───────────────────────────────────────────────────

    public function createMonitor(array $data): PromiseInterface
    {
        return $this->client->postAsync('/balance-monitors', $data);
    }

    public function getMonitor(string $id): PromiseInterface
    {
        return $this->client->getAsync("/balance-monitors/{$id}");
    }

    public function allMonitors(): PromiseInterface
    {
        return $this->client->getAsync('/balance-monitors');
    }

    public function monitorsByBalanceId(string $balanceId): PromiseInterface
    {
        return $this->client->getAsync("/balance-monitors/balances/{$balanceId}");
    }

    public function updateMonitor(string $id, array $data): PromiseInterface
    {
        $data['monitor_id'] = $id;
        return $this->client->putAsync("/balance-monitors/{$id}", $data);
    }

    public function deleteMonitor(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/balance-monitors/{$id}");
    }
}
