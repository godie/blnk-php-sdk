<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Transactions resource.
 *
 * Tests transaction creation, retrieval, filtering, refunds,
 * inflight updates, lineage, and queue recovery.
 */
final class TransactionsTest extends TestCase
{
    private ?string $ledgerId   = null;
    private ?string $sourceId   = null;
    private ?string $destId     = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->hasBlnk) {
            $ledger         = $this->createTestLedger('PHPUnit Txn Test Ledger');
            $this->ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;

            if ($this->ledgerId !== null) {
                $blnk            = $this->requireBlnk();
                $sourceBalance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
                $destBalance     = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'USD']);
                $this->sourceId  = $sourceBalance['balance_id'] ?? $sourceBalance['id'] ?? null;
                $this->destId    = $destBalance['balance_id'] ?? $destBalance['id'] ?? null;
            }
        }
    }

    public function testCreateTransaction(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId, 'Source balance ID must be set');
        $this->assertNotNull($this->destId, 'Destination balance ID must be set');

        $reference = $this->uniqueReference('txn');
        $response  = $blnk->transactions->create([
            'amount'      => 100.00,
            'currency'    => 'USD',
            'reference'   => $reference,
            'description' => 'PHPUnit test transaction',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
            'meta_data'   => ['test' => true, 'sdk' => 'php'],
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testCreateTransactionWithPreciseAmount(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $response = $blnk->transactions->create([
            'precise_amount' => '12345678901234567890',
            'precision'      => 100,
            'currency'       => 'USD',
            'reference'      => $this->uniqueReference('precise'),
            'description'    => 'PHPUnit precise amount test',
            'source'         => $this->sourceId,
            'destination'    => $this->destId,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testCreateTransactionWithoutSourceThrowsException(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->transactions->create([
            'amount'      => 100.00,
            'currency'    => 'USD',
            'reference'   => $this->uniqueReference('fail'),
            'description' => 'Should fail',
            'destination' => $this->destId,
        ]);
    }

    public function testCreateTransactionWithSourcesArray(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $response = $blnk->transactions->create([
            'amount'       => 50.00,
            'currency'     => 'USD',
            'reference'    => $this->uniqueReference('multi-src'),
            'description'  => 'PHPUnit multi-source test',
            'sources'      => [
                ['identifier' => $this->sourceId, 'distribution' => '100'],
            ],
            'destinations' => [
                ['identifier' => $this->destId, 'distribution' => '100'],
            ],
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testCreateBulkTransactions(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $response = $blnk->transactions->createBulk([
            'transactions' => [
                [
                    'amount'      => 10.00,
                    'currency'    => 'USD',
                    'reference'   => $this->uniqueReference('bulk-1'),
                    'description' => 'Bulk txn 1',
                    'source'      => $this->sourceId,
                    'destination' => $this->destId,
                ],
                [
                    'amount'      => 20.00,
                    'currency'    => 'USD',
                    'reference'   => $this->uniqueReference('bulk-2'),
                    'description' => 'Bulk txn 2',
                    'source'      => $this->sourceId,
                    'destination' => $this->destId,
                ],
            ],
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('batch_id', $response);
        $this->assertNotEmpty($response['batch_id'] ?? '');
    }

    public function testGetTransaction(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $reference = $this->uniqueReference('get');
        $created   = $blnk->transactions->create([
            'amount'      => 75.50,
            'currency'    => 'USD',
            'reference'   => $reference,
            'description' => 'PHPUnit get test',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
        ]);

        $txnId = $created['transaction_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($txnId, 'Failed to get transaction_id from create response');

        $fetched = $blnk->transactions->get($txnId);
        $this->assertSuccessfulResponse($fetched);

        $fetchedId = $fetched['transaction_id'] ?? $fetched['id'] ?? null;
        $this->assertEquals($txnId, $fetchedId);
    }

    public function testGetTransactionByReference(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $reference = $this->uniqueReference('by-ref');
        $blnk->transactions->create([
            'amount'      => 33.33,
            'currency'    => 'USD',
            'reference'   => $reference,
            'description' => 'PHPUnit get-by-reference test',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
        ]);

        $fetched = $blnk->transactions->getByReference($reference);
        $this->assertSuccessfulResponse($fetched);
    }

    public function testListAllTransactions(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->transactions->all(5, 0);
        $this->assertSuccessfulResponse($response);

        $transactions = $response['transactions'] ?? $response;
        $this->assertIsArray($transactions);
    }

    public function testFilterTransactions(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->transactions->filter(['currency_eq' => 'USD'], 5, 0);
        $this->assertSuccessfulResponse($response);
    }

    public function testFilterTransactionsWithBody(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->transactions->filterWithBody([
            'limit'          => 5,
            'offset'         => 0,
            'include_count' => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testRefundTransaction(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $created = $blnk->transactions->create([
            'amount'      => 50.00,
            'currency'    => 'USD',
            'reference'   => $this->uniqueReference('refund'),
            'description' => 'PHPUnit refund test',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
        ]);

        $txnId = $created['transaction_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($txnId, 'Failed to get transaction_id');

        $refund = $blnk->transactions->refund($txnId);
        $this->assertSuccessfulResponse($refund);
    }

    public function testCreateInflightTransaction(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $response = $blnk->transactions->create([
            'amount'      => 200.00,
            'currency'    => 'USD',
            'reference'   => $this->uniqueReference('inflight'),
            'description' => 'PHPUnit inflight test',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
            'inflight'    => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testUpdateInflightTransaction(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $created = $blnk->transactions->create([
            'amount'      => 150.00,
            'currency'    => 'USD',
            'reference'   => $this->uniqueReference('inflight-update'),
            'description' => 'PHPUnit inflight update test',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
            'inflight'    => true,
        ]);

        $txnId = $created['transaction_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($txnId);

        // Commit the inflight transaction
        $committed = $blnk->transactions->updateInflightStatus($txnId, 'commit');
        $this->assertSuccessfulResponse($committed);
    }

    public function testGetTransactionLineage(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->sourceId);
        $this->assertNotNull($this->destId);

        $created = $blnk->transactions->create([
            'amount'      => 25.00,
            'currency'    => 'USD',
            'reference'   => $this->uniqueReference('lineage'),
            'description' => 'PHPUnit lineage test',
            'source'      => $this->sourceId,
            'destination' => $this->destId,
        ]);

        $txnId = $created['transaction_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($txnId);

        $lineage = $blnk->transactions->lineage($txnId);
        $this->assertSuccessfulResponse($lineage);
    }

    public function testRecoverQueuedTransactions(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->transactions->recoverQueued('5m');
        $this->assertSuccessfulResponse($response);
    }
}
