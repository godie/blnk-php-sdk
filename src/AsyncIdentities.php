<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncIdentities — Promise-returning identity operations (includes tokenization).
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncIdentities
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new identity (async).
     *
     * @param  array $data {
     *     identity_type, first_name, last_name, other_names, gender,
     *     dob (ISO 8601), email_address, phone_number, nationality,
     *     organization_name, category, street, country, state,
     *     post_code, city, meta_data
     * }
     * @return PromiseInterface<array> The created identity.
     */
    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/identities', $data);
    }

    /**
     * Get an identity by its ID (async).
     *
     * @param  string $id Identity ID (e.g., "id_...").
     * @return PromiseInterface<array> The identity.
     */
    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/identities/{$id}");
    }

    /**
     * Update an existing identity (async).
     *
     * @param  string $id   Identity ID.
     * @param  array  $data Updated identity fields.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function update(string $id, array $data): PromiseInterface
    {
        return $this->client->putAsync("/identities/{$id}", $data);
    }

    /**
     * Delete an identity (async).
     *
     * @param  string $id Identity ID.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function delete(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/identities/{$id}");
    }

    /**
     * List all identities with pagination (async).
     *
     * @param  int   $limit  Number of records (default 20).
     * @param  int   $offset Pagination offset (default 0).
     * @return PromiseInterface<array> Array of identities.
     */
    public function all(int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/identities', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter identities using advanced query-parameter filters (async).
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return PromiseInterface<array> Array of identities.
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/identities', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter identities via JSON body (POST /identities/filter) (async).
     *
     * @param  array $payload Filter payload.
     * @return PromiseInterface<array> Filtered results.
     */
    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/identities/filter', $payload);
    }

    /**
     * Tokenize a specific field in an identity (async).
     *
     * @param  string $id    Identity ID.
     * @param  string $field Field name to tokenize.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function tokenizeField(string $id, string $field): PromiseInterface
    {
        return $this->client->postAsync("/identities/{$id}/tokenize/{$field}");
    }

    /**
     * Detokenize a specific field in an identity (async).
     *
     * @param  string $id    Identity ID.
     * @param  string $field Field name to detokenize.
     * @return PromiseInterface<array> The original field value.
     */
    public function detokenizeField(string $id, string $field): PromiseInterface
    {
        return $this->client->getAsync("/identities/{$id}/detokenize/{$field}");
    }

    /**
     * Tokenize multiple fields in an identity (async).
     *
     * @param  string   $id     Identity ID.
     * @param  string[] $fields Field names to tokenize.
     * @return PromiseInterface<array> Confirmation message.
     */
    public function tokenize(string $id, array $fields): PromiseInterface
    {
        return $this->client->postAsync("/identities/{$id}/tokenize", ['fields' => $fields]);
    }

    /**
     * Detokenize multiple fields (or all) in an identity (async).
     *
     * @param  string   $id     Identity ID.
     * @param  string[] $fields Field names to detokenize (empty = all tokenized fields).
     * @return PromiseInterface<array> The original field values.
     */
    public function detokenize(string $id, array $fields = []): PromiseInterface
    {
        return $this->client->postAsync("/identities/{$id}/detokenize", ['fields' => $fields]);
    }

    /**
     * List all tokenized fields for an identity (async).
     *
     * @param  string $id Identity ID.
     * @return PromiseInterface<array> List of tokenized field names.
     */
    public function tokenizedFields(string $id): PromiseInterface
    {
        return $this->client->getAsync("/identities/{$id}/tokenized-fields");
    }
}
