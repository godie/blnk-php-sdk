<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncMetadata — Promise-returning metadata operations.
 *
 * All methods return PromiseInterface.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncMetadata
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Update metadata for an entity (ledger, transaction, balance, or identity) (async).
     * The entity type is auto-detected by the entity ID prefix.
     *
     * @param  string $entityId Entity ID (e.g., "ldg_...", "txn_...", "bln_...", "id_...").
     * @param  array  $metaData Key-value pairs to set as metadata.
     * @return PromiseInterface The updated metadata.
     */
    public function update(string $entityId, array $metaData): PromiseInterface
    {
        return $this->client->postAsync("/{$entityId}/metadata", ['meta_data' => $metaData]);
    }
}
