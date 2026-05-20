<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Balances — Manage balances and balance monitors.
 *
 * Endpoints covered:
 *   POST   /balances                              Create a balance
 *   GET    /balances/:id                          Get a balance by ID
 *   GET    /balances                              List all balances (paginated)
 *   POST   /balances/filter                       Filter balances via JSON body
 *   GET    /balances/indicator/:indicator/currency/:currency  Get balance by indicator & currency
 *   GET    /balances/:id/at                       Get balance state at a point in time
 *   POST   /balances-snapshots                    Take daily balance snapshots
 *   PUT    /balances/:id/identity                 Update a balance's identity
 *   GET    /balances/:id/lineage                  Get balance lineage
 *
 * Balance Monitor endpoints:
 *   POST   /balance-monitors                      Create a balance monitor
 *   GET    /balance-monitors/:id                  Get a balance monitor
 *   GET    /balance-monitors                      List all balance monitors
 *   GET    /balance-monitors/balances/:balance_id Get monitors by balance ID
 *   PUT    /balance-monitors/:id                  Update a balance monitor
 *   DELETE /balance-monitors/:id                  Delete a balance monitor
 */
class Balances
{
    public function __construct(private BlnkClient $client) {}

    // ─── Balance CRUD ──────────────────────────────────────────────────────

    /**
     * Create a new balance.
     *
     * @param  array $data {
     *     Required: ledger_id, currency
     *     Optional: identity_id, precision, meta_data, track_fund_lineage, allocation_strategy (FIFO|LIFO|PROPORTIONAL)
     * }
     * @return array The created balance.
     * @throws BlnkException
     */
    public function create(array $data): array
    {
        return $this->client->post('/balances', $data);
    }

    /**
     * Get a balance by its ID.
     *
     * @param  string $id         Balance ID (e.g., "bln_...").
     * @param  array  $includes   Optional related data to include.
     * @param  bool   $withQueued Include queued transactions in the balance.
     * @return array              The balance.
     * @throws BlnkException
     */
    public function get(string $id, array $includes = [], bool $withQueued = false): array
    {
        $query = [];
        if ($includes) {
            $query['include'] = $includes;
        }
        if ($withQueued) {
            $query['with_queued'] = 'true';
        }
        return $this->client->get("/balances/{$id}", $query);
    }

    /**
     * List all balances with pagination.
     *
     * @param  int   $limit  Number of records (default 10).
     * @param  int   $offset Pagination offset (default 0).
     * @return array         Array of balances.
     * @throws BlnkException
     */
    public function all(int $limit = 10, int $offset = 0): array
    {
        return $this->client->get('/balances', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter balances using advanced query-parameter filters.
     *
     * Example:
     *   $balances->filter(['currency_eq' => 'USD', 'ledger_id_in' => 'ldg_123,ldg_456']);
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return array          Array of balances.
     * @throws BlnkException
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/balances', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter balances via JSON body (POST /balances/filter).
     *
     * @param  array $payload Filter payload.
     * @return array          Filtered results.
     * @throws BlnkException
     */
    public function filterWithBody(array $payload): array
    {
        return $this->client->post('/balances/filter', $payload);
    }

    /**
     * Get a balance by its indicator and currency.
     *
     * @param  string $indicator The balance indicator.
     * @param  string $currency  The currency code (e.g., "USD").
     * @return array             The balance.
     * @throws BlnkException
     */
    public function getByIndicator(string $indicator, string $currency): array
    {
        return $this->client->get("/balances/indicator/{$indicator}/currency/{$currency}");
    }

    /**
     * Get historical balance state at a specific point in time.
     *
     * @param  string      $balanceId  Balance ID.
     * @param  string|null $timestamp  ISO 8601 timestamp (null = current time).
     * @param  bool        $fromSource Calculate from source transactions.
     * @return array                   Balance state at the given time.
     * @throws BlnkException
     */
    public function getAtTime(string $balanceId, ?string $timestamp = null, bool $fromSource = false): array
    {
        $query = [];
        if ($timestamp !== null) {
            $query['timestamp'] = $timestamp;
        }
        if ($fromSource) {
            $query['from_source'] = 'true';
        }
        return $this->client->get("/balances/{$balanceId}/at", $query);
    }

    /**
     * Trigger daily balance snapshots.
     *
     * @param  int   $batchSize Batch size for processing (default 1000).
     * @return array            Confirmation message.
     * @throws BlnkException
     */
    public function takeSnapshots(int $batchSize = 1000): array
    {
        return $this->client->post('/balances-snapshots', [], ['batch_size' => $batchSize]);
    }

    /**
     * Update a balance's identity.
     *
     * @param  string $balanceId  Balance ID.
     * @param  string $identityId New identity ID.
     * @return array              Confirmation message.
     * @throws BlnkException
     */
    public function updateIdentity(string $balanceId, string $identityId): array
    {
        return $this->client->put("/balances/{$balanceId}/identity", ['identity_id' => $identityId]);
    }

    /**
     * Get the fund lineage for a balance.
     *
     * @param  string $id Balance ID.
     * @return array      Lineage data.
     * @throws BlnkException
     */
    public function lineage(string $id): array
    {
        return $this->client->get("/balances/{$id}/lineage");
    }

    // ─── Balance Monitors ───────────────────────────────────────────────────

    /**
     * Create a balance monitor.
     *
     * @param  array $data {
     *     Required: balance_id, condition (field, operator, value, precision)
     *     Optional: call_back_url, meta_data
     * }
     * @return array The created monitor.
     * @throws BlnkException
     */
    public function createMonitor(array $data): array
    {
        return $this->client->post('/balance-monitors', $data);
    }

    /**
     * Get a balance monitor by its ID.
     *
     * @param  string $id Monitor ID.
     * @return array      The monitor.
     * @throws BlnkException
     */
    public function getMonitor(string $id): array
    {
        return $this->client->get("/balance-monitors/{$id}");
    }

    /**
     * List all balance monitors.
     *
     * @return array Array of monitors.
     * @throws BlnkException
     */
    public function allMonitors(): array
    {
        return $this->client->get('/balance-monitors');
    }

    /**
     * Get balance monitors for a specific balance.
     *
     * @param  string $balanceId Balance ID.
     * @return array             Array of monitors.
     * @throws BlnkException
     */
    public function monitorsByBalanceId(string $balanceId): array
    {
        return $this->client->get("/balance-monitors/balances/{$balanceId}");
    }

    /**
     * Update a balance monitor.
     *
     * @param  string $id   Monitor ID.
     * @param  array  $data Updated monitor data.
     * @return array        Confirmation message.
     * @throws BlnkException
     */
    public function updateMonitor(string $id, array $data): array
    {
        $data['monitor_id'] = $id; // The API expects monitor_id in the body
        return $this->client->put("/balance-monitors/{$id}", $data);
    }

    /**
     * Delete a balance monitor.
     *
     * @param  string $id Monitor ID.
     * @return array      Confirmation message.
     * @throws BlnkException
     */
    public function deleteMonitor(string $id): array
    {
        return $this->client->delete("/balance-monitors/{$id}");
    }
}
