<?php

declare(strict_types=1);

namespace Blnk;

/**
 * Reconciliation — Upload external data, manage matching rules, and run reconciliations.
 *
 * Endpoints covered:
 *   POST   /reconciliation/upload              Upload external transaction data (multipart file)
 *   POST   /reconciliation/matching-rules      Create a matching rule
 *   PUT    /reconciliation/matching-rules/:id  Update a matching rule
 *   DELETE /reconciliation/matching-rules/:id  Delete a matching rule
 *   POST   /reconciliation/start               Start a reconciliation process
 *   POST   /reconciliation/start-instant       Start an instant reconciliation
 *   GET    /reconciliation/:id                 Get reconciliation details
 */
class Reconciliation
{
    public function __construct(private BlnkClient $client) {}

    /**
     * Upload external transaction data (CSV/JSON file).
     *
     * @param  string $filePath Path to the file on disk.
     * @param  string $source   Source identifier (e.g., "bank_statement").
     * @return array            Upload result (upload_id, record_count, source).
     * @throws BlnkException
     */
    public function upload(string $filePath, string $source): array
    {
        return $this->client->uploadFile('/reconciliation/upload', $filePath, [
            'source' => $source,
        ]);
    }

    /**
     * Create a matching rule.
     *
     * @param  array $data {
     *     name, description
     *     criteria[]: { field, operator, value, pattern, allowable_drift }
     * }
     * @return array The created matching rule.
     * @throws BlnkException
     */
    public function createMatchingRule(array $data): array
    {
        return $this->client->post('/reconciliation/matching-rules', $data);
    }

    /**
     * Update an existing matching rule.
     *
     * @param  string $id   Rule ID.
     * @param  array  $data Updated rule data.
     * @return array        The updated matching rule.
     * @throws BlnkException
     */
    public function updateMatchingRule(string $id, array $data): array
    {
        $data['rule_id'] = $id;
        return $this->client->put("/reconciliation/matching-rules/{$id}", $data);
    }

    /**
     * Delete a matching rule.
     *
     * @param  string $id Rule ID.
     * @return array      Confirmation message.
     * @throws BlnkException
     */
    public function deleteMatchingRule(string $id): array
    {
        return $this->client->delete("/reconciliation/matching-rules/{$id}");
    }

    /**
     * Start a reconciliation process on previously uploaded data.
     *
     * @param  string   $uploadId         Upload ID from a prior upload.
     * @param  string   $strategy         Reconciliation strategy (e.g., "one_to_one").
     * @param  string[] $matchingRuleIds  Array of matching rule IDs.
     * @param  string   $groupingCriteria Grouping criteria (optional).
     * @param  bool     $dryRun           Whether to do a dry run (default false).
     * @return array                      Result with reconciliation_id.
     * @throws BlnkException
     */
    public function start(
        string $uploadId,
        string $strategy,
        array $matchingRuleIds,
        string $groupingCriteria = '',
        bool $dryRun = false
    ): array {
        $payload = [
            'upload_id'         => $uploadId,
            'strategy'          => $strategy,
            'matching_rule_ids' => $matchingRuleIds,
            'dry_run'           => $dryRun,
        ];
        if ($groupingCriteria !== '') {
            $payload['grouping_criteria'] = $groupingCriteria;
        }
        return $this->client->post('/reconciliation/start', $payload);
    }

    /**
     * Start an instant reconciliation with externally provided transactions.
     *
     * @param  array    $externalTransactions Array of external transactions.
     * @param  string   $strategy             Reconciliation strategy.
     * @param  string[] $matchingRuleIds      Array of matching rule IDs.
     * @param  string   $groupingCriteria     Grouping criteria (optional).
     * @param  bool     $dryRun               Whether to do a dry run.
     * @return array                          Result with reconciliation_id.
     * @throws BlnkException
     */
    public function startInstant(
        array $externalTransactions,
        string $strategy,
        array $matchingRuleIds,
        string $groupingCriteria = '',
        bool $dryRun = false
    ): array {
        $payload = [
            'external_transactions' => $externalTransactions,
            'strategy'              => $strategy,
            'matching_rule_ids'     => $matchingRuleIds,
            'dry_run'               => $dryRun,
        ];
        if ($groupingCriteria !== '') {
            $payload['grouping_criteria'] = $groupingCriteria;
        }
        return $this->client->post('/reconciliation/start-instant', $payload);
    }

    /**
     * Get reconciliation details by its ID.
     *
     * @param  string $id Reconciliation ID.
     * @return array      Reconciliation details.
     * @throws BlnkException
     */
    public function get(string $id): array
    {
        return $this->client->get("/reconciliation/{$id}");
    }
}
