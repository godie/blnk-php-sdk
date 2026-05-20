<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Accounts resource.
 */
final class AccountsTest extends TestCase
{
    private ?string $ledgerId   = null;
    private ?string $identityId = null;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->hasBlnk) {
            $ledger           = $this->createTestLedger('PHPUnit Account Test Ledger');
            $this->ledgerId   = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
            $identity         = $this->createTestIdentity();
            $this->identityId = $identity['identity_id'] ?? $identity['id'] ?? null;
        }
    }

    public function testCreateAccountViaLedger(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId, 'Ledger ID must be set');
        $this->assertNotNull($this->identityId, 'Identity ID must be set');

        $response = $blnk->accounts->create([
            'ledger_id'   => $this->ledgerId,
            'identity_id' => $this->identityId,
            'currency'    => 'USD',
            'bank_name'   => 'PHPUnit Bank',
            'number'      => '1234567890',
            'meta_data'   => ['test' => true, 'sdk' => 'php'],
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('account_id', $response);
    }

    public function testCreateAccountViaBalance(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);

        $balance   = $blnk->balances->create(['ledger_id' => $this->ledgerId, 'currency' => 'EUR']);
        $balanceId = $balance['balance_id'] ?? $balance['id'] ?? null;
        $this->assertNotNull($balanceId);

        $response = $blnk->accounts->create([
            'balance_id' => $balanceId,
        ]);

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('account_id', $response);
    }

    public function testGetAccount(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);
        $this->assertNotNull($this->identityId);

        $created = $blnk->accounts->create([
            'ledger_id'   => $this->ledgerId,
            'identity_id' => $this->identityId,
            'currency'    => 'USD',
            'number'      => '9876543210',
        ]);

        $accountId = $created['account_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($accountId);

        $fetched = $blnk->accounts->get($accountId);
        $this->assertSuccessfulResponse($fetched);

        $fetchedId = $fetched['account_id'] ?? $fetched['id'] ?? null;
        $this->assertEquals($accountId, $fetchedId);
    }

    public function testGetAccountWithIncludes(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();
        $this->assertNotNull($this->ledgerId);
        $this->assertNotNull($this->identityId);

        $created = $blnk->accounts->create([
            'ledger_id'   => $this->ledgerId,
            'identity_id' => $this->identityId,
            'currency'    => 'USD',
        ]);

        $accountId = $created['account_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($accountId);

        $fetched = $blnk->accounts->get($accountId, ['identity', 'balance']);
        $this->assertSuccessfulResponse($fetched);
    }

    public function testListAllAccounts(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->accounts->all(5, 0);
        $this->assertSuccessfulResponse($response);

        $accounts = $response['accounts'] ?? $response;
        $this->assertIsArray($accounts);
    }

    public function testFilterAccounts(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->accounts->filter(['currency_eq' => 'USD'], 5, 0);
        $this->assertSuccessfulResponse($response);
    }

    public function testFilterAccountsWithBody(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->accounts->filterWithBody([
            'limit'          => 5,
            'offset'         => 0,
            'include_count' => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testMockAccount(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->accounts->mock();
        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('bank_name', $response);
        $this->assertResponseHasField('number', $response);
    }
}
