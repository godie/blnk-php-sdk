<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkClient;
use Blnk\BlnkException;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base TestCase for Blnk PHP SDK integration tests.
 *
 * Provides a pre-configured BlnkClient and helpers to create/clean up
 * test resources. Tests requiring a live Blnk instance should extend
 * this class and call markTestSkippedIfNoBlnk() at the start of each test.
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected ?BlnkClient $blnk = null;
    protected bool $hasBlnk = false;

    /** @var array<string, string> Track created resource IDs for cleanup */
    protected array $createdResources = [];

    protected function setUp(): void
    {
        parent::setUp();

        $baseUrl = getenv('BLNK_BASE_URL') ?: 'http://localhost:5001';
        $apiKey  = getenv('BLNK_API_KEY') ?: '';

        if ($apiKey === '') {
            $this->blnk   = null;
            $this->hasBlnk = false;
            return;
        }

        try {
            $this->blnk = new BlnkClient($baseUrl, $apiKey, [
                'timeout'         => 10,
                'connect_timeout' => 5,
            ]);

            // Quick health check — try to list ledgers
            $this->blnk->ledgers->all(1, 0);
            $this->hasBlnk = true;
        } catch (BlnkException $e) {
            $this->blnk   = null;
            $this->hasBlnk = false;
        } catch (\Throwable $e) {
            $this->blnk   = null;
            $this->hasBlnk = false;
        }
    }

    protected function tearDown(): void
    {
        // Cleanup created resources in reverse order
        foreach (array_reverse($this->createdResources) as $type => $id) {
            try {
                $this->deleteResource($type, $id);
            } catch (\Throwable $e) {
                // Best-effort cleanup; don't fail tests on cleanup errors
            }
        }

        $this->createdResources = [];
        parent::tearDown();
    }

    /**
     * Mark the test as skipped if no Blnk instance is available.
     *
     * Call this at the beginning of every integration test:
     *   $this->markTestSkippedIfNoBlnk();
     */
    protected function markTestSkippedIfNoBlnk(): void
    {
        if (!$this->hasBlnk) {
            $this->markTestSkipped(
                'No Blnk server available. Set BLNK_BASE_URL and BLNK_API_KEY environment variables.'
            );
        }
    }

    /**
     * Require Blnk to be available, and fail with a clear message if not.
     *
     * @return BlnkClient
     */
    protected function requireBlnk(): BlnkClient
    {
        if (!$this->hasBlnk || $this->blnk === null) {
            $this->markTestSkipped(
                'No Blnk server available. Set BLNK_BASE_URL and BLNK_API_KEY environment variables.'
            );
        }

        // Safe: either markTestSkipped threw above, or blnk is set
        \assert($this->blnk !== null);
        return $this->blnk;
    }

    /**
     * Track a created resource for automatic cleanup.
     */
    protected function trackResource(string $type, string $id): void
    {
        $this->createdResources[$type . ':' . $id] = $id;
    }

    /**
     * Best-effort delete a tracked resource by type.
     */
    private function deleteResource(string $type, string $id): void
    {
        if ($this->blnk === null) {
            return;
        }

        switch (true) {
            case str_starts_with($type, 'ledger'):
                // Ledgers cannot be deleted via the API
                break;

            case str_starts_with($type, 'balance_monitor'):
                $this->blnk->balances->deleteMonitor($id);
                break;

            case str_starts_with($type, 'identity'):
                $this->blnk->identities->delete($id);
                break;

            case str_starts_with($type, 'hook'):
                $this->blnk->hooks->delete($id);
                break;

            case str_starts_with($type, 'matching_rule'):
                $this->blnk->reconciliation->deleteMatchingRule($id);
                break;
        }
    }

    /**
     * Create a ledger for use in other tests and track it.
     */
    protected function createTestLedger(string $name = 'PHP SDK Test Ledger'): array
    {
        $blnk   = $this->requireBlnk();
        $ledger = $blnk->ledgers->create($name, ['test' => true, 'sdk' => 'php']);
        $ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        if ($ledgerId !== null) {
            $this->trackResource('ledger', $ledgerId);
        }
        return $ledger;
    }

    /**
     * Create a balance for use in other tests and track it.
     */
    protected function createTestBalance(string $ledgerId, string $currency = 'USD'): array
    {
        $blnk    = $this->requireBlnk();
        $balance = $blnk->balances->create([
            'ledger_id' => $ledgerId,
            'currency'  => $currency,
        ]);
        // Balances don't have a delete endpoint, so we don't track them for cleanup
        return $balance;
    }

    /**
     * Create an identity for use in other tests and track it for cleanup.
     */
    protected function createTestIdentity(): array
    {
        $blnk     = $this->requireBlnk();
        $identity = $blnk->identities->create([
            'identity_type' => 'individual',
            'first_name'    => 'PHP',
            'last_name'     => 'SDK Test',
            'email_address' => 'php-sdk-test@blnkfinance.com',
            'phone_number'  => '+1234567890',
            'country'       => 'US',
        ]);
        $this->trackResource('identity', $identity['identity_id'] ?? $identity['id'] ?? 'unknown');
        return $identity;
    }

    /**
     * Assert that a response array has the expected structure.
     */
    protected function assertSuccessfulResponse(array $response): void
    {
        // Successful responses should not contain an error key at top level
        if (isset($response['error'])) {
            $this->fail('Response contains error: ' . json_encode($response['error']));
        }
    }

    /**
     * Assert that a response contains the given field.
     */
    protected function assertResponseHasField(string $field, array $response): void
    {
        $this->assertArrayHasKey($field, $response, "Response missing field '{$field}'. Got: " . json_encode($response));
    }

    /**
     * Generate a unique reference for transactions.
     */
    protected function uniqueReference(string $prefix = 'test'): string
    {
        return $prefix . '-' . bin2hex(random_bytes(8));
    }
}
