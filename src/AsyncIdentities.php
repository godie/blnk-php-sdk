<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncIdentities — Promise-returning identity operations (includes tokenization).
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncIdentities
{
    public function __construct(private BlnkClient $client) {}

    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/identities', $data);
    }

    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/identities/{$id}");
    }

    public function update(string $id, array $data): PromiseInterface
    {
        return $this->client->putAsync("/identities/{$id}", $data);
    }

    public function delete(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/identities/{$id}");
    }

    public function all(int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/identities', ['limit' => $limit, 'offset' => $offset]);
    }

    public function filter(array $filters = [], int $limit = 20, int $offset = 0): PromiseInterface
    {
        return $this->client->getAsync('/identities', array_merge($filters, [
            'limit'  => $limit,
            'offset' => $offset,
        ]));
    }

    public function filterWithBody(array $payload): PromiseInterface
    {
        return $this->client->postAsync('/identities/filter', $payload);
    }

    public function tokenizeField(string $id, string $field): PromiseInterface
    {
        return $this->client->postAsync("/identities/{$id}/tokenize/{$field}");
    }

    public function detokenizeField(string $id, string $field): PromiseInterface
    {
        return $this->client->getAsync("/identities/{$id}/detokenize/{$field}");
    }

    public function tokenize(string $id, array $fields): PromiseInterface
    {
        return $this->client->postAsync("/identities/{$id}/tokenize", ['fields' => $fields]);
    }

    public function detokenize(string $id, array $fields = []): PromiseInterface
    {
        return $this->client->postAsync("/identities/{$id}/detokenize", ['fields' => $fields]);
    }

    public function tokenizedFields(string $id): PromiseInterface
    {
        return $this->client->getAsync("/identities/{$id}/tokenized-fields");
    }
}
