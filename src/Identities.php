<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Identities — Manage identity records and tokenization.
 *
 * Endpoints covered:
 *   POST   /identities                          Create an identity
 *   GET    /identities/:id                      Get an identity by ID
 *   PUT    /identities/:id                      Update an identity
 *   DELETE /identities/:id                      Delete an identity
 *   GET    /identities                          List all identities (paginated, filterable)
 *   POST   /identities/filter                   Filter identities via JSON body
 *   POST   /identities/:id/tokenize/:field      Tokenize a specific field
 *   GET    /identities/:id/detokenize/:field    Detokenize a specific field
 *   POST   /identities/:id/tokenize             Tokenize multiple fields
 *   POST   /identities/:id/detokenize           Detokenize multiple fields
 *   GET    /identities/:id/tokenized-fields     List tokenized fields
 */
class Identities
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a new identity.
     *
     * @param  array $data {
     *     identity_type, first_name, last_name, other_names, gender,
     *     dob (ISO 8601), email_address, phone_number, nationality,
     *     organization_name, category, street, country, state,
     *     post_code, city, meta_data
     * }
     * @return array The created identity.
     * @throws BlnkException
     */
    public function create(array $data): array
    {
        return $this->client->post('/identities', $data);
    }

    /**
     * Get an identity by its ID.
     *
     * @param  string $id Identity ID (e.g., "id_...").
     * @return array      The identity.
     * @throws BlnkException
     */
    public function get(string $id): array
    {
        return $this->client->get("/identities/{$id}");
    }

    /**
     * Update an existing identity.
     *
     * @param  string $id   Identity ID.
     * @param  array  $data Updated identity fields.
     * @return array        Confirmation message.
     * @throws BlnkException
     */
    public function update(string $id, array $data): array
    {
        return $this->client->put("/identities/{$id}", $data);
    }

    /**
     * Delete an identity.
     *
     * @param  string $id Identity ID.
     * @return array      Confirmation message.
     * @throws BlnkException
     */
    public function delete(string $id): array
    {
        return $this->client->delete("/identities/{$id}");
    }

    /**
     * List all identities with pagination.
     *
     * @param  int   $limit  Number of records (default 20).
     * @param  int   $offset Pagination offset (default 0).
     * @return array         Array of identities.
     * @throws BlnkException
     */
    public function all(int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/identities', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Filter identities using advanced query-parameter filters.
     *
     * @param  array $filters Associative array of field_operator => value.
     * @param  int   $limit   Number of records.
     * @param  int   $offset  Pagination offset.
     * @return array          Array of identities.
     * @throws BlnkException
     */
    public function filter(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/identities', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    /**
     * Filter identities via JSON body (POST /identities/filter).
     *
     * @param  array $payload Filter payload.
     * @return array          Filtered results.
     * @throws BlnkException
     */
    public function filterWithBody(array $payload): array
    {
        return $this->client->post('/identities/filter', $payload);
    }

    /**
     * Tokenize a specific field in an identity.
     *
     * @param  string $id    Identity ID.
     * @param  string $field Field name to tokenize.
     * @return array         Confirmation message.
     * @throws BlnkException
     */
    public function tokenizeField(string $id, string $field): array
    {
        return $this->client->post("/identities/{$id}/tokenize/{$field}");
    }

    /**
     * Detokenize a specific field in an identity.
     *
     * @param  string $id    Identity ID.
     * @param  string $field Field name to detokenize.
     * @return array         The original field value.
     * @throws BlnkException
     */
    public function detokenizeField(string $id, string $field): array
    {
        return $this->client->get("/identities/{$id}/detokenize/{$field}");
    }

    /**
     * Tokenize multiple fields in an identity.
     *
     * @param  string   $id     Identity ID.
     * @param  string[] $fields Field names to tokenize.
     * @return array            Confirmation message.
     * @throws BlnkException
     */
    public function tokenize(string $id, array $fields): array
    {
        return $this->client->post("/identities/{$id}/tokenize", ['fields' => $fields]);
    }

    /**
     * Detokenize multiple fields (or all) in an identity.
     *
     * @param  string   $id     Identity ID.
     * @param  string[] $fields Field names to detokenize (empty = all tokenized fields).
     * @return array            The original field values.
     * @throws BlnkException
     */
    public function detokenize(string $id, array $fields = []): array
    {
        return $this->client->post("/identities/{$id}/detokenize", ['fields' => $fields]);
    }

    /**
     * List all tokenized fields for an identity.
     *
     * @param  string $id Identity ID.
     * @return array      List of tokenized field names.
     * @throws BlnkException
     */
    public function tokenizedFields(string $id): array
    {
        return $this->client->get("/identities/{$id}/tokenized-fields");
    }
}
