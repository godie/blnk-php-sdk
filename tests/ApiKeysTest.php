<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the ApiKeys resource.
 *
 * Note: API key management typically requires the master secret key.
 */
final class ApiKeysTest extends TestCase
{
    public function testCreateApiKey(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->apiKeys->create(
            'PHPUnit Test Key - ' . $this->uniqueReference('key'),
            'phpunit-test-owner',
            ['ledgers:read', 'transactions:write'],
            (new \DateTime('+30 days'))->format(\DateTime::ATOM)
        );

        $this->assertSuccessfulResponse($response);
        $this->assertResponseHasField('api_key', $response);
    }

    public function testCreateApiKeyWithInvalidExpiration(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->apiKeys->create(
            'Bad Key',
            'bad-owner',
            ['ledgers:read'],
            'not-a-date'
        );
    }

    public function testListApiKeys(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $owner = 'phpunit-list-owner-' . $this->uniqueReference();

        // Create at least one key for this owner
        $blnk->apiKeys->create(
            'List Test Key',
            $owner,
            ['ledgers:read'],
            (new \DateTime('+30 days'))->format(\DateTime::ATOM)
        );

        $response = $blnk->apiKeys->all($owner);
        $this->assertSuccessfulResponse($response);

        $keys = $response['api_keys'] ?? $response;
        $this->assertIsArray($keys);
        $this->assertNotEmpty($keys, 'Expected at least one API key for owner');
    }

    public function testRevokeApiKey(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $owner = 'phpunit-revoke-owner-' . $this->uniqueReference();

        // Create a key to revoke
        $created = $blnk->apiKeys->create(
            'Revoke Test Key',
            $owner,
            ['ledgers:read'],
            (new \DateTime('+30 days'))->format(\DateTime::ATOM)
        );

        $keyId = $created['api_key_id'] ?? $created['id'] ?? null;
        // If there's no explicit api_key_id, try to extract from the key object
        if ($keyId === null) {
            $keyId = $created['key_id'] ?? $created['api_key']['id'] ?? null;
        }
        $this->assertNotNull($keyId, 'Failed to get key ID for revocation. Response: ' . json_encode($created));

        $response = $blnk->apiKeys->revoke($keyId, $owner);
        $this->assertIsArray($response); // 204 No Content -> empty array
    }
}
