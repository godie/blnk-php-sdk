<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Ledgers resource.
 *
 * Requires a running Blnk instance (BLNK_BASE_URL + BLNK_API_KEY).
 */
final class LedgersTest extends TestCase
{
    public function testCreateLedger(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->ledgers->create('PHPUnit Test Ledger - ' . $this->uniqueReference(), [
            'env'  => 'test',
            'sdk'  => 'php',
            'test' => true,
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('ledger_id', $response);
        $this->assertEquals('PHPUnit Test Ledger', $response['name'] ?? $response['ledger']['name'] ?? null);
    }

    public function testCreateLedgerWithoutNameThrowsException(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->ledgers->create('');
    }

    public function testGetLedger(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $ledger = $blnk->ledgers->create('PHPUnit Get Test - ' . $this->uniqueReference());
        $ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        $this->assertNotNull($ledgerId, 'Failed to get ledger_id from create response');

        $fetched = $blnk->ledgers->get($ledgerId);
        $this->assertSuccessfulResponse($fetched);

        $fetchedId = $fetched['ledger_id'] ?? $fetched['id'] ?? null;
        $this->assertEquals($ledgerId, $fetchedId);
    }

    public function testGetNonExistentLedgerThrowsException(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->ledgers->get('ldg_nonexistent');
    }

    public function testListAllLedgers(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->ledgers->all(5, 0);
        $this->assertSuccessfulResponse($response);

        // Response could be an array of ledgers or wrapped in 'ledgers'
        $ledgers = $response['ledgers'] ?? $response;
        $this->assertIsArray($ledgers);
    }

    public function testListLedgersWithPagination(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $page1 = $blnk->ledgers->all(2, 0);
        $this->assertSuccessfulResponse($page1);

        $page2 = $blnk->ledgers->all(2, 2);
        $this->assertSuccessfulResponse($page2);
    }

    public function testFilterLedgers(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create a ledger with a specific name for filtering
        $uniqueName = 'FilterTest-' . $this->uniqueReference();
        $blnk->ledgers->create($uniqueName);

        // Filter by name
        $response = $blnk->ledgers->filter(['name_eq' => $uniqueName], 20, 0);
        $this->assertSuccessfulResponse($response);

        // If the response has a 'ledgers' key, unwrap it
        $ledgers = $response['ledgers'] ?? $response;
        $this->assertIsArray($ledgers);
        $this->assertNotEmpty($ledgers, 'Expected to find the ledger with name: ' . $uniqueName);
    }

    public function testFilterLedgersWithBody(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->ledgers->filterWithBody([
            'limit'          => 5,
            'offset'         => 0,
            'include_count' => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testUpdateLedger(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $ledger    = $blnk->ledgers->create('PHPUnit Update Test - ' . $this->uniqueReference());
        $ledgerId  = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        $this->assertNotNull($ledgerId);

        $updated = $blnk->ledgers->update($ledgerId, 'PHPUnit Updated Name - ' . $this->uniqueReference());
        $this->assertSuccessfulResponse($updated);

        // Verify the update
        $fetched = $blnk->ledgers->get($ledgerId);
        $fetchedName = $fetched['name'] ?? $fetched['ledger']['name'] ?? '';
        $this->assertStringContainsString('PHPUnit Updated Name', $fetchedName);
    }
}
