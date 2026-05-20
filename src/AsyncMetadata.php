<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncMetadata — Promise-returning metadata operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncMetadata
{
    public function __construct(private BlnkClient $client) {}

    public function update(string $entityId, array $metaData): PromiseInterface
    {
        return $this->client->postAsync("/{$entityId}/metadata", ['meta_data' => $metaData]);
    }
}
