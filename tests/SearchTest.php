<?php

declare(strict_types=1);

namespace Blnk\Tests;

/**
 * Integration tests for the Search resource.
 */
final class SearchTest extends TestCase
{
    public function testSearchTransactions(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->search('transactions', [
            'q'        => '*',
            'query_by' => 'description',
            'per_page' => 5,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testSearchLedgers(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->search('ledgers', [
            'q'        => '*',
            'query_by' => 'name',
            'per_page' => 5,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testSearchBalances(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->search('balances', [
            'q'        => '*',
            'query_by' => 'currency',
            'per_page' => 5,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testSearchIdentities(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->search('identities', [
            'q'        => '*',
            'query_by' => 'first_name',
            'per_page' => 5,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testMultiSearch(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->multiSearch([
            [
                'collection' => 'transactions',
                'q'          => '*',
                'query_by'   => 'description',
                'per_page'   => 3,
            ],
            [
                'collection' => 'ledgers',
                'q'          => '*',
                'query_by'   => 'name',
                'per_page'   => 3,
            ],
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testStartReindex(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->startReindex(500);
        $this->assertSuccessfulResponse($response);
    }

    public function testGetReindexProgress(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->search->getReindexProgress();
        $this->assertSuccessfulResponse($response);
    }
}
