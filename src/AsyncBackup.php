<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncBackup — Promise-returning backup operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncBackup
{
    public function __construct(private BlnkClient $client) {}

    public function toDisk(): PromiseInterface
    {
        return $this->client->getAsync('/backup');
    }

    public function toS3(): PromiseInterface
    {
        return $this->client->getAsync('/backup-s3');
    }
}
