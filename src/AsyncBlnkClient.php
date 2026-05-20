<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * AsyncBlnkClient — Async companion to BlnkClient.
 *
 * All resource methods return Guzzle PromiseInterface that resolves to
 * the decoded API response array. Use with ->then(), ->wait(), or
 * combine with Blnk\Promises utility.
 *
 * Usage:
 *   $async = $blnk->async();
 *   $async->ledgers->all()->then(fn(array $ledgers) => ...);
 *   $ledgers = $async->ledgers->all()->wait();
 */
class AsyncBlnkClient
{
    public readonly AsyncLedgers $ledgers;
    public readonly AsyncBalances $balances;
    public readonly AsyncTransactions $transactions;
    public readonly AsyncIdentities $identities;
    public readonly AsyncAccounts $accounts;
    public readonly AsyncApiKeys $apiKeys;
    public readonly AsyncHooks $hooks;
    public readonly AsyncReconciliation $reconciliation;
    public readonly AsyncSearch $search;
    public readonly AsyncMetadata $metadata;
    public readonly AsyncBackup $backup;

    public function __construct(private BlnkClient $client)
    {
        $this->ledgers        = new AsyncLedgers($client);
        $this->balances       = new AsyncBalances($client);
        $this->transactions   = new AsyncTransactions($client);
        $this->identities     = new AsyncIdentities($client);
        $this->accounts       = new AsyncAccounts($client);
        $this->apiKeys        = new AsyncApiKeys($client);
        $this->hooks          = new AsyncHooks($client);
        $this->reconciliation = new AsyncReconciliation($client);
        $this->search         = new AsyncSearch($client);
        $this->metadata       = new AsyncMetadata($client);
        $this->backup         = new AsyncBackup($client);
    }

    /**
     * Fire multiple promises concurrently and wait for all to resolve.
     *
     * @param  array<string, PromiseInterface> $promises Named promises.
     * @return array<string, array>                      Resolved results keyed by name.
     * @throws \Throwable If any promise rejects.
     */
    public static function all(array $promises): array
    {
        return Promises::all($promises)->wait();
    }

    /**
     * Fire multiple promises and wait for all to settle (resolve or reject).
     *
     * @param  array<string, PromiseInterface> $promises Named promises.
     * @return array<string, array{state: string, value?: array, reason?: \Throwable}>
     */
    public static function settle(array $promises): array
    {
        return Promises::settle($promises)->wait();
    }

    /**
     * Get the underlying BlnkClient for sync fallback.
     */
    public function sync(): BlnkClient
    {
        return $this->client;
    }
}
