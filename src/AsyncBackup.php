<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncBackup — Promise-returning backup operations.
 *
 * All methods return PromiseInterface<array>.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncBackup
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Create a database backup on disk (async).
     *
     * @return PromiseInterface<array> Confirmation message.
     */
    public function toDisk(): PromiseInterface
    {
        return $this->client->getAsync('/backup');
    }

    /**
     * Create a database backup and store in S3 (async).
     *
     * @return PromiseInterface<array> Confirmation message.
     */
    public function toS3(): PromiseInterface
    {
        return $this->client->getAsync('/backup-s3');
    }
}
