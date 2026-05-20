<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncReconciliation — Promise-returning reconciliation operations.
 *
 * All methods return PromiseInterface<array>.
 */
class AsyncReconciliation
{
    public function __construct(private BlnkClient $client) {}

    public function upload(string $filePath, string $source): PromiseInterface
    {
        return $this->client->uploadFileAsync('/reconciliation/upload', $filePath, [
            'source' => $source,
        ]);
    }

    public function createMatchingRule(array $data): PromiseInterface
    {
        return $this->client->postAsync('/reconciliation/matching-rules', $data);
    }

    public function updateMatchingRule(string $id, array $data): PromiseInterface
    {
        $data['rule_id'] = $id;
        return $this->client->putAsync("/reconciliation/matching-rules/{$id}", $data);
    }

    public function deleteMatchingRule(string $id): PromiseInterface
    {
        return $this->client->deleteAsync("/reconciliation/matching-rules/{$id}");
    }

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

    public function get(string $id): PromiseInterface
    {
        return $this->client->getAsync("/reconciliation/{$id}");
    }
}
