<?php

declare(strict_types=1);

namespace Blnk\Tests;

/**
 * Integration tests for the Metadata resource.
 */
final class MetadataTest extends TestCase
{
    public function testUpdateLedgerMetadata(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $ledger   = $blnk->ledgers->create('PHPUnit Metadata Ledger');
        $ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        $this->assertNotNull($ledgerId);

        $response = $blnk->metadata->update($ledgerId, [
            'test_timestamp' => date('c'),
            'sdk_version'    => 'php',
            'test'           => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testUpdateTransactionMetadata(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create ledger, balances, txn
        $ledger   = $blnk->ledgers->create('PHPUnit Meta Txn Ledger');
        $ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        $this->assertNotNull($ledgerId);

        $src    = $blnk->balances->create(['ledger_id' => $ledgerId, 'currency' => 'USD']);
        $dest   = $blnk->balances->create(['ledger_id' => $ledgerId, 'currency' => 'USD']);
        $srcId  = $src['balance_id'] ?? $src['id'] ?? null;
        $destId = $dest['balance_id'] ?? $dest['id'] ?? null;
        $this->assertNotNull($srcId);
        $this->assertNotNull($destId);

        $txn = $blnk->transactions->create([
            'amount'      => 5.00,
            'currency'    => 'USD',
            'reference'   => $this->uniqueReference('meta-txn'),
            'description' => 'Metadata update test',
            'source'      => $srcId,
            'destination' => $destId,
        ]);

        $txnId = $txn['transaction_id'] ?? $txn['id'] ?? null;
        $this->assertNotNull($txnId);

        $response = $blnk->metadata->update($txnId, [
            'updated_by' => 'phpunit',
            'meta_test'  => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testUpdateBalanceMetadata(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $ledger   = $blnk->ledgers->create('PHPUnit Meta Bal Ledger');
        $ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        $this->assertNotNull($ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $ledgerId, 'currency' => 'USD']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $response = $blnk->metadata->update($balanceId, [
            'balance_note' => 'Updated by PHPUnit',
            'test'         => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testUpdateIdentityMetadata(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $identity   = $this->createTestIdentity();
        $identityId = $identity['identity_id'] ?? $identity['id'] ?? null;
        $this->assertNotNull($identityId);

        $response = $blnk->metadata->update($identityId, [
            'risk_level'  => 'low',
            'verified_at' => date('c'),
        ]);

        $this->assertSuccessfulResponse($response);
    }
}
