<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Identities resource including tokenization.
 */
final class IdentitiesTest extends TestCase
{
    public function testCreateIdentity(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->identities->create([
            'identity_type' => 'individual',
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'email_address' => 'john.doe@example.com',
            'phone_number'  => '+1987654321',
            'country'       => 'US',
            'city'          => 'New York',
            'meta_data'     => ['test' => true, 'sdk' => 'php'],
        ]);

        $this->assertSuccessfulResponse($response);
        $identityId = $response['identity_id'] ?? $response['id'] ?? null;
        $this->assertNotNull($identityId, 'Failed to get identity_id');
        $this->trackResource('identity', $identityId);
    }

    public function testCreateOrganizationIdentity(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->identities->create([
            'identity_type'     => 'organization',
            'organization_name' => 'PHPUnit Test Org',
            'email_address'     => 'org@example.com',
            'country'           => 'US',
            'category'          => 'technology',
            'meta_data'         => ['test' => true],
        ]);

        $this->assertSuccessfulResponse($response);
        $identityId = $response['identity_id'] ?? $response['id'] ?? null;
        $this->assertNotNull($identityId);
        $this->trackResource('identity', $identityId);
    }

    public function testGetIdentity(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        $fetched = $blnk->identities->get($identityId);
        $this->assertSuccessfulResponse($fetched);

        $fetchedId = $fetched['identity_id'] ?? $fetched['id'] ?? null;
        $this->assertEquals($identityId, $fetchedId);
    }

    public function testGetNonExistentIdentityThrowsException(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->identities->get('id_nonexistent');
    }

    public function testUpdateIdentity(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        $updated = $blnk->identities->update($identityId, [
            'first_name' => 'Updated',
            'last_name'  => 'Name',
            'city'       => 'San Francisco',
        ]);

        $this->assertSuccessfulResponse($updated);
    }

    public function testListAllIdentities(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->identities->all(5, 0);
        $this->assertSuccessfulResponse($response);

        $identities = $response['identities'] ?? $response;
        $this->assertIsArray($identities);
    }

    public function testFilterIdentities(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->identities->filter(['country_eq' => 'US'], 5, 0);
        $this->assertSuccessfulResponse($response);
    }

    public function testFilterIdentitiesWithBody(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->identities->filterWithBody([
            'limit'          => 5,
            'offset'         => 0,
            'include_count' => true,
        ]);

        $this->assertSuccessfulResponse($response);
    }

    public function testDeleteIdentity(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $identity = $blnk->identities->create([
            'identity_type' => 'individual',
            'first_name'    => 'Delete',
            'last_name'     => 'Me',
            'country'       => 'US',
        ]);

        $identityId = $identity['identity_id'] ?? $identity['id'] ?? null;
        $this->assertNotNull($identityId);

        $response = $blnk->identities->delete($identityId);
        $this->assertIsArray($response);
    }

    // ─── Tokenization ───────────────────────────────────────────────────────

    public function testTokenizeSingleField(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        $response = $blnk->identities->tokenizeField($identityId, 'email_address');
        $this->assertSuccessfulResponse($response);
    }

    public function testDetokenizeSingleField(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        // Tokenize first
        $blnk->identities->tokenizeField($identityId, 'email_address');

        // Then detokenize
        $response = $blnk->identities->detokenizeField($identityId, 'email_address');
        $this->assertSuccessfulResponse($response);
    }

    public function testTokenizeMultipleFields(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        $response = $blnk->identities->tokenize($identityId, ['email_address', 'phone_number']);
        $this->assertSuccessfulResponse($response);
    }

    public function testDetokenizeMultipleFields(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        // Tokenize first
        $blnk->identities->tokenize($identityId, ['email_address']);

        // Detokenize all
        $response = $blnk->identities->detokenize($identityId, []);
        $this->assertSuccessfulResponse($response);
    }

    public function testListTokenizedFields(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created    = $this->createTestIdentity();
        $identityId = $created['identity_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($identityId);

        // Tokenize a field first
        $blnk->identities->tokenizeField($identityId, 'email_address');

        $response = $blnk->identities->tokenizedFields($identityId);
        $this->assertSuccessfulResponse($response);
    }
}
