<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Backup resource.
 */
final class BackupTest extends TestCase
{
    public function testBackupToDisk(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->backup->toDisk();
        $this->assertSuccessfulResponse($response);
    }

    public function testBackupToS3(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // S3 backup may fail if not configured, but the SDK call should work
        try {
            $response = $blnk->backup->toS3();
            $this->assertIsArray($response);
        } catch (BlnkException $e) {
            // S3 backup may not be configured, which is fine for tests
            $this->assertStringContainsStringIgnoringCase('s3', $e->getMessage());
        }
    }
}
