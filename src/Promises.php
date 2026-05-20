<?php

declare(strict_types=1);

namespace Blnk;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;

/**
 * Promises — Convenience methods for working with Guzzle promises
 * returned by the Blnk async SDK methods.
 *
 * Usage:
 *   // Fire 3 requests concurrently, fail if any fail
 *   $results = Promises::all([
 *       'ledgers'  => $async->ledgers->all(),
 *       'balances' => $async->balances->all(),
 *       'accounts' => $async->accounts->all(),
 *   ])->wait();
 *
 *   // Fire 3 requests, get all results regardless of errors
 *   $settled = Promises::settle([...])->wait();
 */
final class Promises
{
    /**
     * Combine multiple promises. Resolves when ALL resolve.
     * Rejects immediately if ANY reject.
     *
     * @param  array<string, PromiseInterface> $promises
     * @return PromiseInterface
     */
    public static function all(array $promises): PromiseInterface
    {
        return Utils::all($promises);
    }

    /**
     * Combine multiple promises. Resolves when ALL settle (resolve OR reject).
     * Never rejects — check each result's 'state' field.
     *
     * @param  array<string, PromiseInterface> $promises
     * @return PromiseInterface
     */
    public static function settle(array $promises): PromiseInterface
    {
        return Utils::settle($promises);
    }

    /**
     * Resolve a promise and return its value synchronously.
     *
     * @param  PromiseInterface $promise
     * @return array
     * @throws \Throwable If the promise rejects.
     */
    public static function unwrap(PromiseInterface $promise): array
    {
        return $promise->wait();
    }

    /**
     * Create a resolved promise with the given value.
     *
     * @return PromiseInterface
     */
    public static function resolved(array $value): PromiseInterface
    {
        return \GuzzleHttp\Promise\Create::promiseFor($value);
    }

    /**
     * Create a rejected promise with the given exception.
     *
     * @return PromiseInterface
     */
    public static function rejected(\Throwable $reason): PromiseInterface
    {
        return \GuzzleHttp\Promise\Create::rejectionFor($reason);
    }
}
