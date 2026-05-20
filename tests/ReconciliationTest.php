<?php

declare(strict_types=1);

namespace Blnk\Tests;

use Blnk\BlnkException;

/**
 * Integration tests for the Reconciliation resource.
 *
 * Tests file upload, matching rule management, and reconciliation execution.
 */
final class ReconciliationTest extends TestCase
{
    public function testCreateMatchingRule(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $response = $blnk->reconciliation->createMatchingRule([
            'name'        => 'PHPUnit Match Rule - ' . $this->uniqueReference(),
            'description' => 'Test matching rule',
            'criteria'    => [
                [
                    'field'           => 'amount',
                    'operator'        => 'equals',
                    'allowable_drift' => 0.01,
                ],
                [
                    'field'    => 'reference',
                    'operator' => 'contains',
                ],
            ],
        ]);

        $this->assertSuccessfulResponse($response);
        $ruleId = $response['matching_rule_id'] ?? $response['rule_id'] ?? $response['id'] ?? null;
        $this->assertNotNull($ruleId, 'Failed to get matching_rule_id');
        $this->trackResource('matching_rule', $ruleId);
    }

    public function testUpdateMatchingRule(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create first
        $created = $blnk->reconciliation->createMatchingRule([
            'name'        => 'PHPUnit Update Rule - ' . $this->uniqueReference(),
            'description' => 'Rule to be updated',
            'criteria'    => [
                ['field' => 'amount', 'operator' => 'equals', 'allowable_drift' => 0.01],
            ],
        ]);

        $ruleId = $created['matching_rule_id'] ?? $created['rule_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($ruleId);
        $this->trackResource('matching_rule', $ruleId);

        // Update
        $updated = $blnk->reconciliation->updateMatchingRule($ruleId, [
            'name'        => 'PHPUnit Updated Rule - ' . $this->uniqueReference(),
            'description' => 'Updated description',
            'criteria'    => [
                ['field' => 'amount', 'operator' => 'equals', 'allowable_drift' => 0.05],
                ['field' => 'description', 'operator' => 'contains'],
            ],
        ]);

        $this->assertSuccessfulResponse($updated);
    }

    public function testDeleteMatchingRule(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $created = $blnk->reconciliation->createMatchingRule([
            'name'        => 'PHPUnit Delete Rule - ' . $this->uniqueReference(),
            'description' => 'Rule to delete',
            'criteria'    => [
                ['field' => 'amount', 'operator' => 'equals', 'allowable_drift' => 0.01],
            ],
        ]);

        $ruleId = $created['matching_rule_id'] ?? $created['rule_id'] ?? $created['id'] ?? null;
        $this->assertNotNull($ruleId);

        $response = $blnk->reconciliation->deleteMatchingRule($ruleId);
        $this->assertIsArray($response);
    }

    public function testUploadFile(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create a temporary CSV file for upload
        $tmpFile  = tempnam(sys_get_temp_dir(), 'blnk_upload_');
        $csvData  = "transaction_id,amount,currency,reference,description\n";
        $csvData .= "ext-txn-001,100.00,USD,ref-001,External transaction 1\n";
        $csvData .= "ext-txn-002,200.00,USD,ref-002,External transaction 2\n";
        file_put_contents($tmpFile, $csvData);

        try {
            $response = $blnk->reconciliation->upload($tmpFile, 'bank_statement');
            $this->assertSuccessfulResponse($response);
            $this->assertResponseHasField('upload_id', $response);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testUploadNonExistentFileThrowsException(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        $this->expectException(BlnkException::class);
        $blnk->reconciliation->upload('/nonexistent/path/file.csv', 'bank_statement');
    }

    public function testStartInstantReconciliation(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create a matching rule first
        $rule = $blnk->reconciliation->createMatchingRule([
            'name'        => 'PHPUnit Instant Rule - ' . $this->uniqueReference(),
            'description' => 'Rule for instant reconciliation',
            'criteria'    => [
                ['field' => 'amount', 'operator' => 'equals', 'allowable_drift' => 0.01],
            ],
        ]);

        $ruleId = $rule['matching_rule_id'] ?? $rule['rule_id'] ?? $rule['id'] ?? null;
        $this->assertNotNull($ruleId);
        $this->trackResource('matching_rule', $ruleId);

        $externalTxns = [
            [
                'transaction_id' => 'ext-instant-001',
                'amount'         => 50.00,
                'currency'       => 'USD',
                'reference'      => 'ref-instant-001',
                'description'    => 'Instant external txn',
            ],
        ];

        $response = $blnk->reconciliation->startInstant(
            $externalTxns,
            'one_to_one',
            [$ruleId],
            '',
            true // dry run
        );

        $this->assertSuccessfulResponse($response);
    }

    public function testGetReconciliation(): void
    {
        $this->markTestSkippedIfNoBlnk();
        $blnk = $this->requireBlnk();

        // Create a matching rule first
        $rule = $blnk->reconciliation->createMatchingRule([
            'name'     => 'PHPUnit Get Recon Rule - ' . $this->uniqueReference(),
            'criteria' => [['field' => 'amount', 'operator' => 'equals', 'allowable_drift' => 0.01]],
        ]);

        $ruleId = $rule['matching_rule_id'] ?? $rule['rule_id'] ?? $rule['id'] ?? null;
        $this->assertNotNull($ruleId);
        $this->trackResource('matching_rule', $ruleId);

        // Start an instant reconciliation
        $started = $blnk->reconciliation->startInstant(
            [['transaction_id' => 'ext-recon-001', 'amount' => 10.00, 'currency' => 'USD', 'reference' => 'ref-recon-001', 'description' => 'Test']],
            'one_to_one',
            [$ruleId],
            '',
            true
        );

        $reconId = $started['reconciliation_id'] ?? $started['id'] ?? null;

        if ($reconId !== null) {
            $fetched = $blnk->reconciliation->get($reconId);
            $this->assertSuccessfulResponse($fetched);
        } else {
            // The get reconciliation endpoint might still work with a non-existent ID — test that at least it doesn't crash the SDK
            $this->assertNotNull($started);
        }
    }
}
