<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncHooks — Promise-returning webhook operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncHooks
{
    public function __construct(private BlnkClient $client) {}

    public function create(array $data): PromiseInterface
    {
        return $this->client->postAsync('/hooks', $data);
    }

    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/hooks/{$id}");
    }

    public function update(string $id, array $data): PromiseInterface
    {
        return $this->client->putAsync("/hooks/{$id}", $data);
    }

    public function all(?string $type = null): PromiseInterface
    {
        $query = $type ? ['type' => $type] : [];
        return $this->client->getAsync('/hooks', $query);
    }

    public function delete(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/hooks/{$id}");
    }
}
