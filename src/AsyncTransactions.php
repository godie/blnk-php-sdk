<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncTransactions — Promise-returning transaction operations.
 *
 * All methods return PromiseInterface.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncTransactions
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Queue (create) a new transaction (async).
     *
     * @param  array $data {
     *     Required: amount (or precise_amount), currency, reference, description
     *     At least one of: source or sources, destination or destinations
     *     Optional: precision, rate, allow_overdraft, inflight, scheduled_for,
     *              inflight_expiry_date, inflight_commit_date, skip_queue, atomic,
     *              sources[], destinations[], meta_data, effective_date,
     *              overdraft_limit, precise_amount
     * }
     * @return PromiseInterface The created transaction.
     */
    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/transactions', $data);
    }

    /**
     * Create multiple transactions in a single batch (async).
     *
     * @param  array $data {
     *     transactions: Transaction[]
     *     Optional: inflight, atomic, run_async, skip_queue
     * }
     * @return PromiseInterface Batch result (includes batch_id, status, transaction_count).
     */
    public function createBulk(array $data): PromiseInterface
    {
        return $this->client->postAsync('/transactions/bulk', $data);
    }

    /**
     * Get a transaction by its ID (async).
     *
     * @param  string $id Transaction ID (e.g., "txn_...").
     * @return PromiseInterface The transaction.
     */
    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/transactions/{$id}");
    }

    /**
     * Get a transaction by its reference (async).
     *
     * @param  string $reference Transaction reference.
     * @return PromiseInterface The transaction.
     */
    public function getByReference(string $reference): PromiseInterface
    {
        return $this->client->getAsync("/transactions/reference/{$reference}");
    }

    /**
     * List all transactions with optional pagination (async).
     *
     * @param  int   $limit  Number of records (default 20).
     * @param  int   $offset Pagination offset (default 0).
     * @return PromiseInterface Array of transactions.
     */
    public function all(int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/transactions', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter transactions using advanced query-parameter filters (async).
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return PromiseInterface Array of transactions.
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/transactions', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter transactions via JSON body (POST /transactions/filter) (async).
     *
     * @param  array $payload Filter payload.
     * @return PromiseInterface Filtered results.
     */
    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/transactions/filter', $payload);
    }

    /**
     * Refund a transaction by its ID (async).
     *
     * @param  string $id Transaction ID to refund.
     * @return PromiseInterface The refund transaction.
     */
    public function refund(string $id): PromiseInterface
    {
        return $this->client->postAsync("/refund-transaction/{$id}");
    }

    /**
     * Update the status of an inflight transaction (commit or void) (async).
     *
     * @param  string      $txId   Transaction ID.
     * @param  string      $status "commit" or "void".
     * @param  float       $amount Amount (optional, if different from original).
     * @return PromiseInterface Updated transaction.
     */
    public function updateInflightStatus(string $txId, string $status, float $amount = 0): PromiseInterface
    {
        $body = ['status' => $status];
        if ($amount > 0) {
            $body['amount'] = $amount;
        }
        return $this->client->putAsync("/transactions/inflight/{$txId}", $body);
    }

    /**
     * Get the fund lineage for a transaction (async).
     *
     * @param  string $id Transaction ID.
     * @return PromiseInterface Lineage data.
     */
    public function lineage(string $id): PromiseInterface
    {
        return $this->client->getAsync("/transactions/{$id}/lineage");
    }

    /**
     * Recover stuck queued transactions (async).
     *
     * @param  string $threshold Minimum age of transactions to recover (e.g., "5m", "10m"). Defaults to "2m".
     * @return PromiseInterface Recovery result with count of recovered transactions.
     */
    public function recoverQueued(string $threshold = '2m'): PromiseInterface
    {
        return $this->client->postAsync('/transactions/recover', [], ['threshold' => $threshold]);
    }
}
