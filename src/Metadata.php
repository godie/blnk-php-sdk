<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Metadata — Update metadata on any Blnk entity.
 *
 * Endpoints covered:
 *   POST   /:entity-id/metadata   Update metadata for a ledger, transaction, balance, or identity
 */
class Metadata
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Update metadata for an entity (ledger, transaction, balance, or identity).
     * The entity type is auto-detected by the entity ID prefix.
     *
     * @param  string $entityId Entity ID (e.g., "ldg_...", "txn_...", "bln_...", "id_...").
     * @param  array  $metaData Key-value pairs to set as metadata.
     * @return array            The updated metadata.
     * @throws BlnkException
     */
    public function update(string $entityId, array $metaData): array
    {
        return $this->client->post("/{$entityId}/metadata", ['meta_data' => $metaData]);
    }
}
