<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkClient;
use Blnk\BlnkException;

/**
 * Unit/integration tests for the BlnkClient itself.
 *
 * Tests HTTP client behavior, error handling, and edge cases.
 */
final class BlnkClientTest extends TestCase
{
    public function testClientInitialization(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->assertInstanceOf(BlnkClient::class, $blnk);
    }

    public function testResourcePropertiesAreInitialized(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->assertNotNull($blnk->ledgers);
        $this->assertNotNull($blnk->balances);
        $this->assertNotNull($blnk->transactions);
        $this->assertNotNull($blnk->identities);
        $this->assertNotNull($blnk->accounts);
        $this->assertNotNull($blnk->apiKeys);
        $this->assertNotNull($blnk->hooks);
        $this->assertNotNull($blnk->reconciliation);
        $this->assertNotNull($blnk->search);
        $this->assertNotNull($blnk->metadata);
        $this->assertNotNull($blnk->backup);
    }

    public function testGetRequest(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->get('/ledgers', ['limit' => 1]);
        $this->assertIsArray($response);
    }

    public function testPostRequest(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->post('/ledgers', ['name' => 'PHPUnit Direct Post Test']);
        $this->assertIsArray($response);
        $this->assertResponseHasField('ledger_id', $response);
    }

    public function testPutRequest(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create a ledger first
        $ledger   = $blnk->ledgers->create('PHPUnit Put Test');
        $ledgerId = $ledger['ledger_id'] ?? $ledger['id'] ?? null;
        $this->assertNotNull($ledgerId);

        $response = $blnk->put("/ledgers/{$ledgerId}", ['name' => 'PHPUnit Put Test Updated']);
        $this->assertIsArray($response);
    }

    public function testDeleteRequest(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Delete a hook (we create one first)
        $hook = $blnk->hooks->create([
            'name' => 'PHPUnit Delete Client Test',
            'url'  => 'https://example.com/delete-client-test',
            'type' => 'POST_TRANSACTION',
        ]);

        $hookId = $hook['hook_id'] ?? $hook['id'] ?? null;
        $this->assertNotNull($hookId);

        $response = $blnk->delete("/hooks/{$hookId}");
        $this->assertIsArray($response); // 204 -> empty array
    }

    public function testExceptionOnInvalidBaseUrl(): void
    {
        $this->expectException(BlnkException::class);

        $client = new BlnkClient('http://localhost:19999', 'fake-key', [
            'timeout'         => 2,
            'connect_timeout' => 1,
        ]);

        $client->get('/ledgers');
    }

    public function testExceptionOnInvalidApiKey(): void
    {
        $baseUrl = getenv('BLNK_BASE_URL') ?: 'http://localhost:5001';

        $this->expectException(BlnkException::class);

        $client = new BlnkClient($baseUrl, 'invalid-api-key-12345', [
            'timeout'         => 5,
            'connect_timeout' => 2,
        ]);

        $client->get('/ledgers');
    }

    public function testBaseUrlTrailingSlashIsTrimmed(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $baseUrl = getenv('BLNK_BASE_URL') ?: 'http://localhost:5001';
        $apiKey  = getenv('BLNK_API_KEY') ?: '';

        // Create client with trailing slash — should still work
        $client = new BlnkClient($baseUrl . '/', $apiKey, [
            'timeout' => 5,
        ]);

        $response = $client->get('/ledgers', ['limit' => 1]);
        $this->assertIsArray($response);
    }

    public function testClientWithCustomOptions(): void
    {
        $baseUrl = getenv('BLNK_BASE_URL') ?: 'http://localhost:5001';
        $apiKey  = getenv('BLNK_API_KEY') ?: '';

        if ($apiKey === '') {
            $this->markTestSkipped('BLNK_API_KEY not set');
        }

        $client = new BlnkClient($baseUrl, $apiKey, [
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Blnk-PHP-SDK-Test/1.0',
            ],
        ]);

        $response = $client->get('/ledgers', ['limit' => 1]);
        $this->assertIsArray($response);
    }

    public function testEmptyResponseHandling(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Delete a hook and verify we get an empty array back (204 No Content)
        $hook = $blnk->hooks->create([
            'name' => 'PHPUnit Empty Response Test',
            'url'  => 'https://example.com/empty-test',
            'type' => 'POST_TRANSACTION',
        ]);

        $hookId = $hook['hook_id'] ?? $hook['id'] ?? null;
        $this->assertNotNull($hookId);

        $response = $blnk->hooks->delete($hookId);
        $this->assertIsArray($response);
        $this->assertEmpty($response, 'Expected empty array for 204 No Content');
    }

    public function testBlobResponseHandling(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Mock account returns a JSON response that should decode to an array
        $response = $blnk->accounts->mock();
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }
}
