<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Accounts — Manage accounts linked to identities and balances.
 *
 * Endpoints covered:
 *   POST   /accounts         Create an account
 *   GET    /accounts/:id     Get an account by ID
 *   GET    /accounts         List all accounts (paginated, filterable)
 *   POST   /accounts/filter  Filter accounts via JSON body
 *   GET    /mocked-account   Generate a mock account
 */
class Accounts
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new account.
     *
     * @param  array $data {
     *     Create via Ledger:  ledger_id, identity_id, currency (required)
     *     Create via Balance: balance_id (required)
     *     Optional: bank_name, number, meta_data
     * }
     * @return array The created account.
     * @throws BlnkException
     */
    public function create(array $data): array
    {
        return $this->client->post('/accounts', $data);
    }

    /**
     * Get an account by its ID.
     *
     * @param  string   $id       Account ID (e.g., "acc_...").
     * @param  string[] $includes Optional related data to include.
     * @return array              The account.
     * @throws BlnkException
     */
    public function get(string $id, array $includes = []): array
    {
        $query = $includes ? ['include' => $includes] : [];
        return $this->client->get("/accounts/{$id}", $query);
    }

    /**
     * List all accounts with pagination.
     *
     * @param  int   $limit  Number of records (default 20).
     * @param  int   $offset Pagination offset (default 0).
     * @return array         Array of accounts.
     * @throws BlnkException
     */
    public function all(int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/accounts', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter accounts using advanced query-parameter filters.
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return array          Array of accounts.
     * @throws BlnkException
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/accounts', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter accounts via JSON body (POST /accounts/filter).
     *
     * @param  array $payload Filter payload.
     * @return array          Filtered results.
     * @throws BlnkException
     */
    public function filterWithBody(array $payload): array
    {
        return $this->client->post('/accounts/filter', $payload);
    }

    /**
     * Generate a mock account for testing.
     *
     * @return array Mock account with bank_name and account_number.
     * @throws BlnkException
     */
    public function mock(): array
    {
        return $this->client->get('/mocked-account');
    }
}
