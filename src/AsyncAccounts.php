<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncAccounts — Promise-returning account operations.
 *
 * All methods return PromiseInterface.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncAccounts
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new account (async).
     *
     * @param  array $data {
     *     Create via Ledger:  ledger_id, identity_id, currency (required)
     *     Create via Balance: balance_id (required)
     *     Optional: bank_name, number, meta_data
     * }
     * @return PromiseInterface The created account.
     */
    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/accounts', $data);
    }

    /**
     * Get an account by its ID (async).
     *
     * @param  string   $id       Account ID (e.g., "acc_...").
     * @param  string[] $includes Optional related data to include.
     * @return PromiseInterface The account.
     */
    public function get(string $id, array $includes = []): PromiseInterface
    {
        $query = $includes ? ['include' => $includes] : [];
        return $this->client->getAsync("/accounts/{$id}", $query);
    }

    /**
     * List all accounts with pagination (async).
     *
     * @param  int   $limit  Number of records (default 20).
     * @param  int   $offset Pagination offset (default 0).
     * @return PromiseInterface Array of accounts.
     */
    public function all(int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/accounts', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter accounts using advanced query-parameter filters (async).
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return PromiseInterface Array of accounts.
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/accounts', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter accounts via JSON body (POST /accounts/filter) (async).
     *
     * @param  array $payload Filter payload.
     * @return PromiseInterface Filtered results.
     */
    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/accounts/filter', $payload);
    }

    /**
     * Generate a mock account for testing (async).
     *
     * @return PromiseInterface Mock account with bank_name and account_number.
     */
    public function mock(): PromiseInterface
    {
        return $this->client->getAsync('/mocked-account');
    }
}
