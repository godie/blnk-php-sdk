<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncReconciliation — Promise-returning reconciliation operations.
 *
 * All methods return PromiseInterface.
 *
 * Exception handling: BlnkException errors propagate through the promise
 * chain. Callers should handle rejections via ->then(null, $onRejected) or
 * try/catch around ->wait().
 */
class AsyncReconciliation
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Upload external transaction data (CSV/JSON file) (async).
     *
     * @param  string $filePath Path to the file on disk.
     * @param  string $source   Source identifier (e.g., "bank_statement").
     * @return PromiseInterface Upload result (upload_id, record_count, source).
     */
    public function upload(string $filePath, string $source): PromiseInterface
    {
        return $this->client->uploadFileAsync('/reconciliation/upload', $filePath, [
            'source' => $source,
        ]);
    }

    /**
     * Create a matching rule (async).
     *
     * @param  array $data {
     *     name, description
     *     criteria[]: { field, operator, value, pattern, allowable_drift }
     * }
     * @return PromiseInterface The created matching rule.
     */
    public function createMatchingRule(array $data): PromiseInterface
    {
        return $this->client->postAsync('/reconciliation/matching-rules', $data);
    }

    /**
     * Update an existing matching rule (async).
     *
     * @param  string $id   Rule ID.
     * @param  array  $data Updated rule data.
     * @return PromiseInterface The updated matching rule.
     */
    public function updateMatchingRule(string $id, array $data): PromiseInterface
    {
        $data['rule_id'] = $id;
        return $this->client->putAsync("/reconciliation/matching-rules/{$id}", $data);
    }

    /**
     * Delete a matching rule (async).
     *
     * @param  string $id Rule ID.
     * @return PromiseInterface Confirmation message.
     */
    public function deleteMatchingRule(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/reconciliation/matching-rules/{$id}");
    }

    /**
     * Start a reconciliation process on previously uploaded data (async).
     *
     * @param  string   $uploadId         Upload ID from a prior upload.
     * @param  string   $strategy         Reconciliation strategy (e.g., "one_to_one").
     * @param  string[] $matchingRuleIds  Array of matching rule IDs.
     * @param  string   $groupingCriteria Grouping criteria (optional).
     * @param  bool     $dryRun           Whether to do a dry run (default false).
     * @return PromiseInterface    Result with reconciliation_id.
     */
    public function start(
        string $uploadId,
        string $strategy,
        array $matchingRuleIds,
        string $groupingCriteria = '',
        bool $dryRun = false
    ): PromiseInterface {
        $payload = [
            'upload_id'         => $uploadId,
            'strategy'          => $strategy,
            'matching_rule_ids' => $matchingRuleIds,
            'dry_run'           => $dryRun,
        ];
        if ($groupingCriteria !== '') {
            $payload['grouping_criteria'] = $groupingCriteria;
        }
        return $this->client->postAsync('/reconciliation/start', $payload);
    }

    /**
     * Start an instant reconciliation with externally provided transactions (async).
     *
     * @param  array    $externalTransactions Array of external transactions.
     * @param  string   $strategy             Reconciliation strategy.
     * @param  string[] $matchingRuleIds      Array of matching rule IDs.
     * @param  string   $groupingCriteria     Grouping criteria (optional).
     * @param  bool     $dryRun               Whether to do a dry run.
     * @return PromiseInterface        Result with reconciliation_id.
     */
    public function startInstant(
        array $externalTransactions,
        string $strategy,
        array $matchingRuleIds,
        string $groupingCriteria = '',
        bool $dryRun = false
    ): PromiseInterface {
        $payload = [
            'external_transactions' => $externalTransactions,
            'strategy'              => $strategy,
            'matching_rule_ids'     => $matchingRuleIds,
            'dry_run'               => $dryRun,
        ];
        if ($groupingCriteria !== '') {
            $payload['grouping_criteria'] = $groupingCriteria;
        }
        return $this->client->postAsync('/reconciliation/start-instant', $payload);
    }

    /**
     * Get reconciliation details by its ID (async).
     *
     * @param  string $id Reconciliation ID.
     * @return PromiseInterface Reconciliation details.
     */
    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/reconciliation/{$id}");
    }
}
