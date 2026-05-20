<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Backup — Database backup operations.
 *
 * Endpoints covered:
 *   GET    /backup      Backup database to disk
 *   GET    /backup-s3   Backup database to S3
 */
class Backup
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a database backup on disk.
     *
     * @return array Confirmation message.
     * @throws BlnkException
     */
    public function toDisk(): array
    {
        return $this->client->get('/backup');
    }

    /**
     * Create a database backup and store in S3.
     *
     * @return array Confirmation message.
     * @throws BlnkException
     */
    public function toS3(): array
    {
        return $this->client->get('/backup-s3');
    }
}
