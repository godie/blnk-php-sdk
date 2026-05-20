<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncBalances — Promise-returning balance operations (includes monitors).
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncBalances
{
    public function __construct(private BlnkClient $client) {}

    // ─── Balance CRUD ──────────────────────────────────────────────────────

    /**
     * Create a new balance (async).
     *
     * @param  array $data {
     *     Required: ledger_id, currency
     *     Optional: identity_id, precision, meta_data, track_fund_lineage, allocation_strategy (FIFO|LIFO|PROPORTIONAL)
     * }
     * @return PromiseInterface<array> The created balance.
     */
    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/balances', $data);
    }

    /**
     * Get a balance by its ID (async).
     *
     * @param  string $id         Balance ID (e.g., "bln_...").
     * @param  array  $includes   Optional related data to include.
     * @param  bool   $withQueued Include queued transactions in the balance.
     * @return PromiseInterface<array> The balance.
     */
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

    /**
     * List all balances with pagination (async).
     *
     * @param  int   $limit  Number of records (default 10).
     * @param  int   $offset Pagination offset (default 0).
     * @return PromiseInterface<array> Array of balances.
     */
    public function all(int $limit = 10, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/balances', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter balances using advanced query-parameter filters (async).
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return PromiseInterface<array> Array of balances.
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/balances', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter balances via JSON body (POST /balances/filter) (async).
     *
     * @param  array $payload Filter payload.
     * @return PromiseInterface<array> Filtered results.
     */
    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/balances/filter', $payload);
    }

    /**
     * Get a balance by its indicator and currency (async).
     *
     * @param  string $indicator The balance indicator.
     * @param  string $currency  The currency code (e.g., "USD").
     * @return PromiseInterface<array> The balance.
     */
    public function getByIndicator(string $indicator, string $currency): PromiseInterface
    {
        return $this->client->getAsync("/balances/indicator/{$indicator}/currency/{$currency}");
    }

    /**
     * Get historical balance state at a specific point in time (async).
     *
     * @param  string      $balanceId  Balance ID.
     * @param  string|null $timestamp  ISO 8601 timestamp (null = current time).
     * @param  bool        $fromSource Calculate from source transactions.
     * @return PromiseInterface<array> Balance state at the given time.
     */
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

    /**
     * Trigger daily balance snapshots (async).
     *
     * @param  int   $batchSize Batch size for processing (default 1000).
     * @return PromiseInterface<array> Confirmation message.
     */
    public function takeSnapshots(int $batchSize = 1000): PromiseInterface
    {
        return $this->client->postAsync('/balances-snapshots', [], ['batch_size' => $batchSize]);
    }

    /**
     * Update a balance's identity (async).
     *
     * @param  string $balanceId  Balance ID.
     * @param  string $identityId New identity ID.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function updateIdentity(string $balanceId, string $identityId): PromiseInterface
    {
        return $this->client->putAsync("/balances/{$balanceId}/identity", ['identity_id' => $identityId]);
    }

    /**
     * Get the fund lineage for a balance (async).
     *
     * @param  string $id Balance ID.
     * @return PromiseInterface<array> Lineage data.
     */
    public function lineage(string $id): PromiseInterface
    {
        return $this->client->getAsync("/balances/{$id}/lineage");
    }

    // ─── Balance Monitors ───────────────────────────────────────────────────

    /**
     * Create a balance monitor (async).
     *
     * @param  array $data {
     *     Required: balance_id, condition (field, operator, value, precision)
     *     Optional: call_back_url, meta_data
     * }
     * @return PromiseInterface<array> The created monitor.
     */
    public function createMonitor(array $data): PromiseInterface
    {
        return $this->client->postAsync('/balance-monitors', $data);
    }

    /**
     * Get a balance monitor by its ID (async).
     *
     * @param  string $id Monitor ID.
     * @return PromiseInterface<array> The monitor.
     */
    public function getMonitor(string $id): PromiseInterface
    {
        return $this->client->getAsync("/balance-monitors/{$id}");
    }

    /**
     * List all balance monitors (async).
     *
     * @return PromiseInterface<array> Array of monitors.
     */
    public function allMonitors(): PromiseInterface
    {
        return $this->client->getAsync('/balance-monitors');
    }

    /**
     * Get balance monitors for a specific balance (async).
     *
     * @param  string $balanceId Balance ID.
     * @return PromiseInterface<array> Array of monitors.
     */
    public function monitorsByBalanceId(string $balanceId): PromiseInterface
    {
        return $this->client->getAsync("/balance-monitors/balances/{$balanceId}");
    }

    /**
     * Update a balance monitor (async).
     *
     * @param  string $id   Monitor ID.
     * @param  array  $data Updated monitor data.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function updateMonitor(string $id, array $data): PromiseInterface
    {
        $data['monitor_id'] = $id;
        return $this->client->putAsync("/balance-monitors/{$id}", $data);
    }

    /**
     * Delete a balance monitor (async).
     *
     * @param  string $id Monitor ID.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function deleteMonitor(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/balance-monitors/{$id}");
    }
}
