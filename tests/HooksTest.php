<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Hooks (webhooks) resource.
 */
final class HooksTest extends TestCase
{
    private ?string $hookId = null;

    public function testCreateHook(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->hooks->create([
            'name'        => 'PHPUnit Test Hook - ' . $this->uniqueReference(),
            'url'         => 'https://example.com/blnk-webhook',
            'type'        => 'POST_TRANSACTION',
            'active'      => true,
            'timeout'     => 30,
            'retry_count' => 3,
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('hook_id', $response);

        $this->hookId = $response['hook_id'] ?? $response['id'] ?? null;
        $this->trackResource('hook', $this->hookId ?? '');
    }

    public function testCreatePreTransactionHook(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->hooks->create([
            'name' => 'PHPUnit Pre-Txn Hook - ' . $this->uniqueReference(),
            'url'  => 'https://example.com/pre-txn-webhook',
            'type' => 'PRE_TRANSACTION',
        ]);

        $this->assertSuccessfulResponse($response);
        $hookId = $response['hook_id'] ?? $response['id'] ?? null;
        $this->trackResource('hook', $hookId ?? '');
    }

    public function testGetHook(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created = $blnk->hooks->create([
            'name' => 'PHPUnit Get Hook - ' . $this->uniqueReference(),
            'url'  => 'https://example.com/get-hook',
            'type' => 'POST_TRANSACTION',
        ]);

        $hookId = $created['hook_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($hookId);
        $this->trackResource('hook', $hookId);

        $fetched = $blnk->hooks->get($hookId);
        $this->assertSuccessfulResponse($fetched);

        $fetchedId = $fetched['hook_id'] ?? $fetched['id'] ?? null;
        $this->assertEquals($hookId, $fetchedId);
    }

    public function testUpdateHook(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created = $blnk->hooks->create([
            'name' => 'PHPUnit Update Hook - ' . $this->uniqueReference(),
            'url'  => 'https://example.com/old-url',
            'type' => 'POST_TRANSACTION',
        ]);

        $hookId = $created['hook_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($hookId);
        $this->trackResource('hook', $hookId);

        $updated = $blnk->hooks->update($hookId, [
            'url'    => 'https://example.com/new-url',
            'active' => false,
        ]);

        $this->assertSuccessfulResponse($updated);
    }

    public function testListAllHooks(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->hooks->all();
        $this->assertSuccessfulResponse($response);

        $hooks = $response['hooks'] ?? $response;
        $this->assertIsArray($hooks);
    }

    public function testListHooksFilteredByType(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $preResponse = $blnk->hooks->all('PRE_TRANSACTION');
        $this->assertSuccessfulResponse($preResponse);

        $postResponse = $blnk->hooks->all('POST_TRANSACTION');
        $this->assertSuccessfulResponse($postResponse);
    }

    public function testDeleteHook(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created = $blnk->hooks->create([
            'name' => 'PHPUnit Delete Hook - ' . $this->uniqueReference(),
            'url'  => 'https://example.com/delete-me',
            'type' => 'POST_TRANSACTION',
        ]);

        $hookId = $created['hook_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($hookId);

        $response = $blnk->hooks->delete($hookId);
        $this->assertIsArray($response);
    }
}
