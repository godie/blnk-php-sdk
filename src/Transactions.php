<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Transactions — Record, query, and manage financial transactions.
 *
 * Endpoints covered:
 *   POST   /transactions                        Queue a transaction
 *   POST   /transactions/bulk                   Create bulk transactions
 *   POST   /transactions/filter                 Filter transactions via JSON body
 *   POST   /refund-transaction/:id              Refund a transaction
 *   GET    /transactions                        List all transactions (paginated, filterable)
 *   GET    /transactions/:id                    Get a transaction by ID
 *   GET    /transactions/reference/:reference   Get a transaction by reference
 *   PUT    /transactions/inflight/:txID         Update inflight transaction status
 *   GET    /transactions/:id/lineage            Get transaction lineage
 *   POST   /transactions/recover                Recover queued transactions
 */
class Transactions
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Queue (create) a new transaction.
     *
     * @param  array $data {
     *     Required: amount (or precise_amount), currency, reference, description
     *     At least one of: source or sources, destination or destinations
     *     Optional: precision, rate, allow_overdraft, inflight, scheduled_for,
     *              inflight_expiry_date, inflight_commit_date, skip_queue, atomic,
     *              sources[], destinations[], meta_data, effective_date,
     *              overdraft_limit, precise_amount
     * }
     * @return array The created transaction.
     * @throws BlnkException
     */
    public function create(array $data): array
    {
        return $this->client->post('/transactions', $data);
    }

    /**
     * Create multiple transactions in a single batch.
     *
     * @param  array $data {
     *     transactions: Transaction[]
     *     Optional: inflight, atomic, run_async, skip_queue
     * }
     * @return array Batch result (includes batch_id, status, transaction_count).
     * @throws BlnkException
     */
    public function createBulk(array $data): array
    {
        return $this->client->post('/transactions/bulk', $data);
    }

    /**
     * Get a transaction by its ID.
     *
     * @param  string $id Transaction ID (e.g., "txn_...").
     * @return array      The transaction.
     * @throws BlnkException
     */
    public function get(string $id): array
    {
        return $this->client->get("/transactions/{$id}");
    }

    /**
     * Get a transaction by its reference.
     *
     * @param  string $reference Transaction reference.
     * @return array             The transaction.
     * @throws BlnkException
     */
    public function getByReference(string $reference): array
    {
        return $this->client->get("/transactions/reference/{$reference}");
    }

    /**
     * List all transactions with optional pagination.
     * Supports advanced query-parameter filters (e.g., status_eq=APPLIED).
     *
     * @param  int   $limit  Number of records (default 20).
     * @param  int   $offset Pagination offset (default 0).
     * @return array         Array of transactions.
     * @throws BlnkException
     */
    public function all(int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/transactions', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter transactions using advanced query-parameter filters.
     *
     * Example:
     *   $txns->filter(['status_eq' => 'APPLIED', 'currency_in' => 'USD,EUR']);
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return array          Array of transactions.
     * @throws BlnkException
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/transactions', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter transactions via JSON body (POST /transactions/filter).
     *
     * @param  array $payload Filter payload.
     * @return array          Filtered results.
     * @throws BlnkException
     */
    public function filterWithBody(array $payload): array
    {
        return $this->client->post('/transactions/filter', $payload);
    }

    /**
     * Refund a transaction by its ID.
     *
     * @param  string $id Transaction ID to refund.
     * @return array      The refund transaction.
     * @throws BlnkException
     */
    public function refund(string $id): array
    {
        return $this->client->post("/refund-transaction/{$id}");
    }

    /**
     * Update the status of an inflight transaction (commit or void).
     *
     * @param  string      $txId   Transaction ID.
     * @param  string      $status "commit" or "void".
     * @param  float       $amount Amount (optional, if different from original).
     * @return array               Updated transaction.
     * @throws BlnkException
     */
    public function updateInflightStatus(string $txId, string $status, float $amount = 0): array
    {
        $body = ['status' => $status];
        if ($amount > 0) {
            $body['amount'] = $amount;
        }
        return $this->client->put("/transactions/inflight/{$txId}", $body);
    }

    /**
     * Get the fund lineage for a transaction.
     *
     * @param  string $id Transaction ID.
     * @return array      Lineage data.
     * @throws BlnkException
     */
    public function lineage(string $id): array
    {
        return $this->client->get("/transactions/{$id}/lineage");
    }

    /**
     * Recover stuck queued transactions.
     *
     * @param  string $threshold Minimum age of transactions to recover (e.g., "5m", "10m").
     *                           Defaults to "2m".
     * @return array             Recovery result with count of recovered transactions.
     * @throws BlnkException
     */
    public function recoverQueued(string $threshold = '2m'): array
    {
        return $this->client->post('/transactions/recover', [], ['threshold' => $threshold]);
    }
}
