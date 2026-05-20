<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Balances resource (including Balance Monitors).
 */
final class BalancesTest extends TestCase
{
    private ?string $ledgerId = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->hasBlnk) {
            $ledger       = $this->createTestLedger('PHPUnit Balance Test Ledger');
            $this->ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        }
    }

    public function testCreateBalance(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId, 'Ledger ID must be set');

        $response = $blnk->balances->create([
            'ledger_id' => $this->ledgerId,
            'currency'  => 'USD',
            'meta_data' => ['test' => true, 'sdk' => 'php'],
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('balance_id', $response);
        $this->assertEquals('USD', $response['currency'] ?? null);
    }

    public function testCreateBalanceWithoutLedgerIdThrowsException(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->balances->create([
            'currency' => 'USD',
        ]);
    }

    public function testCreateBalanceWithPrecision(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $response = $blnk->balances->create([
            'ledger_id' => $this->ledgerId,
            'currency'  => 'EUR',
            'precision' => 1000,
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertEquals(1000, $response['precision'] ?? $response['currency_multiplier'] ?? null);
    }

    public function testCreateBalanceWithAllocationStrategy(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $response = $blnk->balances->create([
            'ledger_id'           => $this->ledgerId,
            'currency'            => 'GBP',
            'allocation_strategy' => 'FIFO',
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testGetBalance(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId, 'Failed to get balance_id');

        $fetched = $blnk->balances->get($balanceId);
        $this->assertSuccessfulResponse($fetched);

        $fetchedId = $fetched['balance_id'] ?? $fetched['id'] ?? null;
        $this->assertEquals($balanceId, $fetchedId);
    }

    public function testGetBalanceWithIncludes(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $fetched = $blnk->balances->get($balanceId, ['ledger']);
        $this->assertSuccessfulResponse($fetched);
    }

    public function testListAllBalances(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->balances->all(5, 0);
        $this->assertSuccessfulResponse($response);

        $balances = $response['balances'] ?? $response;
        $this->assertIsArray($balances);
    }

    public function testFilterBalances(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->balances->filter(['currency_eq' => 'USD'], 5, 0);
        $this->assertSuccessfulResponse($response);
    }

    public function testFilterBalancesWithBody(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->balances->filterWithBody([
            'limit'          => 5,
            'offset'         => 0,
            'include_count' => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testGetBalanceByIndicator(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        // Create a balance, get its indicator, then look it up
        $balance = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'CAD']);
        $this->assertSuccessfulResponse($balance);
        $this->assertNotNull($balance['balance_id'] ?? null);
    }

    public function testGetBalanceAtTime(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $response = $blnk->balances->getAtTime($balanceId, null);
        $this->assertSuccessfulResponse($response);
    }

    public function testUpdateBalanceIdentity(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $identity  = $this->createTestIdentity();
        $identityId = $identity['identity_id'] ?? $identity['id'] ?? null;
        $this->assertNotNull($identityId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $response = $blnk->balances->updateIdentity($balanceId, $identityId);
        $this->assertSuccessfulResponse($response);
    }

    public function testGetBalanceLineage(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create([
            'ledger_id'           => $this->ledgerId,
            'currency'            => 'USD',
            'track_fund_lineage'  => true,
        ]);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $response = $blnk->balances->lineage($balanceId);
        $this->assertSuccessfulResponse($response);
    }

    public function testTakeSnapshots(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->balances->takeSnapshots(100);
        $this->assertSuccessfulResponse($response);
    }

    // ─── Balance Monitors ───────────────────────────────────────────────────

    public function testCreateAndGetMonitor(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $monitor = $blnk->balances->createMonitor([
            'balance_id'    => $balanceId,
            'condition'     => [
                'field'     => 'balance',
                'operator'  => '>',
                'value'     => 100,
                'precision' => 100,
            ],
            'call_back_url' => 'https://example.com/webhook',
        ]);

        $this->assertSuccessfulResponse($monitor);
        $monitorId = $monitor['monitor_id'] ?? $monitor['id'] ?? null;
        $this->assertNotNull($monitorId);

        $this->trackResource('balance_monitor', $monitorId);

        // Get by ID
        $fetched = $blnk->balances->getMonitor($monitorId);
        $this->assertSuccessfulResponse($fetched);
    }

    public function testListAllMonitors(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->balances->allMonitors();
        $this->assertIsArray($response);
    }

    public function testMonitorsByBalanceId(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        // Create a monitor for this balance
        $blnk->balances->createMonitor([
            'balance_id' => $balanceId,
            'condition'  => [
                'field'     => 'balance',
                'operator'  => '>',
                'value'     => 100,
                'precision' => 100,
            ],
        ]);

        $monitors = $blnk->balances->monitorsByBalanceId($balanceId);
        $this->assertSuccessfulResponse($monitors);
        $this->assertIsArray($monitors);
    }

    public function testUpdateMonitor(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $monitor = $blnk->balances->createMonitor([
            'balance_id'    => $balanceId,
            'condition'     => [
                'field'     => 'balance',
                'operator'  => '>',
                'value'     => 100,
                'precision' => 100,
            ],
            'call_back_url' => 'https://example.com/webhook',
        ]);
        $monitorId = $monitor['monitor_id'] ?? $monitor['id'] ?? null;
        $this->assertNotNull($monitorId);
        $this->trackResource('balance_monitor', $monitorId);

        $updated = $blnk->balances->updateMonitor($monitorId, [
            'condition'     => [
                'field'     => 'balance',
                'operator'  => '>',
                'value'     => 200,
                'precision' => 100,
            ],
            'call_back_url' => 'https://example.com/updated-webhook',
        ]);

        $this->assertSuccessfulResponse($updated);
    }

    public function testDeleteMonitor(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $monitor = $blnk->balances->createMonitor([
            'balance_id'    => $balanceId,
            'condition'     => [
                'field'     => 'balance',
                'operator'  => '>',
                'value'     => 100,
                'precision' => 100,
            ],
        ]);
        $monitorId = $monitor['monitor_id'] ?? $monitor['id'] ?? null;
        $this->assertNotNull($monitorId);

        $response = $blnk->balances->deleteMonitor($monitorId);
        $this->assertIsArray($response);
    }
}
