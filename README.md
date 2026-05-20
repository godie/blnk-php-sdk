# blnk-php-sdk

Official PHP client library for the [Blnk Finance](https://blnkfinance.com) API — a programmable double-entry ledger for financial applications.

## Requirements

- PHP 8.1 or later
- [Composer](https://getcomposer.org/)
- [Guzzle](https://docs.guzzlephp.org/) 7.x (auto-installed via Composer)

## Installation

Install via Composer:

```bash
composer require godie/blnk-php-sdk
```

If the package isn't published on Packagist yet, you can install it directly from GitHub by adding this to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/godie/blnk-php-sdk"
        }
    ],
    "require": {
        "godie/blnk-php-sdk": "dev-main"
    }
}
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use Blnk\BlnkClient;

// Initialize the client with your Blnk server URL and API key
$blnk = new BlnkClient('http://localhost:5001', 'your-master-key-or-api-key');

// Create a ledger
$ledger = $blnk->ledgers->create('Main Operations Ledger');
echo $ledger['ledger_id']; // "ldg_abc123..."

// Create a balance
$balance = $blnk->balances->create([
    'ledger_id' => $ledger['ledger_id'],
    'currency'  => 'USD',
]);
echo $balance['balance_id']; // "bln_xyz789..."

// Record a transaction
$txn = $blnk->transactions->create([
    'amount'      => 100.00,
    'currency'    => 'USD',
    'source'      => $balance['balance_id'],
    'destination' => 'external_account_123',
    'reference'   => 'INV-2024-001',
    'description' => 'Payment for invoice INV-2024-001',
]);

// Get a transaction
$txn = $blnk->transactions->get($txn['transaction_id']);

// Refund a transaction
$refund = $blnk->transactions->refund($txn['transaction_id']);
```

## Resources

### Ledgers

```php
// Create
$blnk->ledgers->create('Ledger Name', ['key' => 'value']);

// Get by ID
$blnk->ledgers->get('ldg_xxx');

// List all (paginated)
$blnk->ledgers->all(10, 0);

// Filter with query params
$blnk->ledgers->filter(['name_eq' => 'USD Ledger'], 20, 0);

// Filter with JSON body (advanced)
$blnk->ledgers->filterWithBody([
    'filters' => [
        ['field' => 'name', 'operator' => 'ilike', 'value' => '%savings%']
    ],
    'limit' => 20,
    'offset' => 0,
    'include_count' => true,
]);

// Update
$blnk->ledgers->update('ldg_xxx', 'New Name');
```

### Balances

```php
// Create
$blnk->balances->create([
    'ledger_id'          => 'ldg_xxx',
    'currency'           => 'USD',
    'identity_id'        => 'id_xxx',       // optional
    'precision'          => 100,             // multiplier (100 = 2 decimal places)
    'track_fund_lineage' => true,
    'allocation_strategy' => 'FIFO',         // FIFO | LIFO | PROPORTIONAL
]);

// Get by ID
$blnk->balances->get('bln_xxx', ['include' => ['ledger']]);

// Get by indicator & currency
$blnk->balances->getByIndicator('my-indicator', 'USD');

// Historical balance at a point in time
$blnk->balances->getAtTime('bln_xxx', '2024-06-01T00:00:00Z');

// Take snapshots
$blnk->balances->takeSnapshots(1000);

// Update identity
$blnk->balances->updateIdentity('bln_xxx', 'id_yyy');

// Fund lineage
$blnk->balances->lineage('bln_xxx');
```

#### Balance Monitors

```php
// Create
$blnk->balances->createMonitor([
    'balance_id'   => 'bln_xxx',
    'condition'    => [
        'field'     => 'balance',
        'operator'  => '>',
        'value'     => 10000,
        'precision' => 100,
    ],
    'call_back_url' => 'https://example.com/webhook',
]);

// Get / List / Update / Delete
$blnk->balances->getMonitor('mon_xxx');
$blnk->balances->allMonitors();
$blnk->balances->monitorsByBalanceId('bln_xxx');
$blnk->balances->updateMonitor('mon_xxx', ['call_back_url' => '...']);
$blnk->balances->deleteMonitor('mon_xxx');
```

### Transactions

```php
// Create (queue)
$blnk->transactions->create([
    'amount'      => 50.00,
    'currency'    => 'USD',
    'source'      => 'bln_source',
    'destination' => 'bln_dest',
    'reference'   => 'ORDER-123',
    'description' => 'Order payment',
    // optional:
    'precision'   => 100,
    'inflight'    => false,
    'scheduled_for' => '2024-12-25T00:00:00Z',
    'meta_data'   => ['order_id' => '123'],
]);

// Multi-source / multi-destination
$blnk->transactions->create([
    'amount'       => 100.00,
    'currency'     => 'USD',
    'reference'    => 'SPLIT-001',
    'description'  => 'Split payment',
    'sources'      => [
        ['identifier' => 'bln_wallet_a', 'distribution' => '50%'],
        ['identifier' => 'bln_wallet_b', 'distribution' => '50%'],
    ],
    'destinations' => [
        ['identifier' => 'bln_merchant', 'distribution' => 'left'],
    ],
]);

// Bulk transactions
$blnk->transactions->createBulk([
    'transactions' => [
        ['amount' => 10, 'currency' => 'USD', 'source' => 'bln_a', 'destination' => 'bln_b', 'reference' => 'B1', 'description' => 'Batch 1'],
        ['amount' => 20, 'currency' => 'USD', 'source' => 'bln_c', 'destination' => 'bln_d', 'reference' => 'B2', 'description' => 'Batch 2'],
    ],
    'run_async' => false,
]);

// Get / List / Filter
$blnk->transactions->get('txn_xxx');
$blnk->transactions->getByReference('ORDER-123');
$blnk->transactions->all(20, 0);
$blnk->transactions->filter(['status_eq' => 'APPLIED'], 20, 0);
$blnk->transactions->filterWithBody([...]);

// Refund
$blnk->transactions->refund('txn_xxx');

// Inflight status (commit/void)
$blnk->transactions->updateInflightStatus('txn_xxx', 'commit');
$blnk->transactions->updateInflightStatus('txn_xxx', 'void');

// Lineage & recovery
$blnk->transactions->lineage('txn_xxx');
$blnk->transactions->recoverQueued('5m');
```

### Identities

```php
// Create
$blnk->identities->create([
    'identity_type' => 'individual',
    'first_name'    => 'Jane',
    'last_name'     => 'Doe',
    'email_address' => 'jane@example.com',
    'category'      => 'customer',
    'country'       => 'US',
]);

// Get / List / Update / Delete
$blnk->identities->get('id_xxx');
$blnk->identities->all(20, 0);
$blnk->identities->update('id_xxx', ['first_name' => 'John']);
$blnk->identities->delete('id_xxx');

// Tokenization (for PII data protection)
$blnk->identities->tokenize('id_xxx', ['email_address', 'phone_number']);
$blnk->identities->detokenize('id_xxx', ['email_address']);
$blnk->identities->tokenizeField('id_xxx', 'email_address');
$blnk->identities->detokenizeField('id_xxx', 'email_address');
$blnk->identities->tokenizedFields('id_xxx');
```

### Accounts

```php
// Create via ledger
$blnk->accounts->create([
    'ledger_id'   => 'ldg_xxx',
    'identity_id' => 'id_xxx',
    'currency'    => 'USD',
    'bank_name'   => 'Blnk Bank',
    'number'      => '1234567890',
]);

// Create via existing balance
$blnk->accounts->create(['balance_id' => 'bln_xxx']);

// Get / List / Filter
$blnk->accounts->get('acc_xxx', ['include' => ['ledger', 'balance']]);
$blnk->accounts->all(20, 0);
$blnk->accounts->filterWithBody([...]);

// Mock account for testing
$blnk->accounts->mock();
```

### API Keys

```php
// Create (requires master key)
$blnk->apiKeys->create(
    'My Service Key',
    'service-owner',
    ['ledgers:read', 'transactions:write'],
    '2025-12-31T23:59:59Z'
);

// List
$blnk->apiKeys->all('service-owner');

// Revoke
$blnk->apiKeys->revoke('apk_xxx', 'service-owner');
```

### Hooks (Webhooks)

```php
// Register
$blnk->hooks->create([
    'name'    => 'Pre-transaction validation',
    'url'     => 'https://example.com/hooks/validate',
    'type'    => 'PRE_TRANSACTION',
    'active'  => true,
    'timeout' => 30,
]);

// Get / List / Update / Delete
$blnk->hooks->get('hook_xxx');
$blnk->hooks->all('PRE_TRANSACTION');
$blnk->hooks->update('hook_xxx', ['active' => false]);
$blnk->hooks->delete('hook_xxx');
```

### Reconciliation

```php
// Upload external data (CSV/JSON file)
$blnk->reconciliation->upload('/path/to/statement.csv', 'bank_statement');

// Create matching rules
$blnk->reconciliation->createMatchingRule([
    'name'        => 'Match on reference',
    'description' => 'Match by transaction reference',
    'criteria'    => [
        ['field' => 'reference', 'operator' => 'exact_match'],
    ],
]);

// Start reconciliation
$blnk->reconciliation->start(
    'upload_xxx',
    'one_to_one',
    ['rule_xxx'],
    '',      // grouping_criteria (optional)
    false    // dry_run
);

// Instant reconciliation (no upload needed)
$blnk->reconciliation->startInstant(
    [
        ['id' => 'ext1', 'amount' => 100, 'reference' => 'REF001', 'currency' => 'USD', 'date' => '2024-01-15T00:00:00Z'],
    ],
    'one_to_one',
    ['rule_xxx']
);

// Get reconciliation status
$blnk->reconciliation->get('rec_xxx');
```

### Search

```php
// Search within a collection
$blnk->search->search('transactions', [
    'q'        => 'USD',
    'query_by' => 'currency,description',
]);

// Multi-collection search
$blnk->search->multiSearch([
    ['collection' => 'transactions', 'q' => 'refund', 'query_by' => 'description'],
    ['collection' => 'ledgers',     'q' => 'main',   'query_by' => 'name'],
]);

// Reindex
$blnk->search->startReindex(1000);
$blnk->search->getReindexProgress();
```

### Metadata

```php
// Update metadata on any entity
$blnk->metadata->update('ldg_xxx', ['department' => 'finance', 'region' => 'US']);
$blnk->metadata->update('txn_xxx', ['category' => 'subscription']);
```

### Backup

```php
$blnk->backup->toDisk();
$blnk->backup->toS3();
```

## Authentication

The SDK authenticates via the `X-Blnk-Key` header. You can use:
- Your **master secret key** (set in `blnk.json` → `server.secret_key`) — full access
- A **scoped API key** — limited permissions based on the key's defined scopes

```php
// Master key
$blnk = new BlnkClient('https://blnk.example.com:5001', 'your-master-secret');

// Scoped API key
$blnk = new BlnkClient('https://blnk.example.com:5001', 'blnk_apk_xxx...');
```

## Async / Promise-based API

The SDK provides async variants of all resource methods via `$blnk->async()`. Async methods return `GuzzleHttp\Promise\PromiseInterface` which resolve to the same `array` responses as their sync counterparts.

```php
<?php

use Blnk\BlnkClient;
use Blnk\Promises;

$blnk  = new BlnkClient('http://localhost:5001', 'your-api-key');
$async = $blnk->async();

// ── Single request ─────────────────────────────────────────────
$async->ledgers->all(10, 0)
    ->then(function (array $ledgers) {
        echo "Found " . count($ledgers) . " ledgers\n";
    })
    ->wait(); // blocks until resolved

// Or just wait synchronously:
$ledgers = $async->ledgers->all(10, 0)->wait();

// ── Concurrent requests ────────────────────────────────────────
$results = Promises::all([
    'ledgers'  => $async->ledgers->all(),
    'balances' => $async->balances->all(),
    'accounts' => $async->accounts->all(),
])->wait();

echo count($results['ledgers']) . " ledgers\n";
echo count($results['balances']) . " balances\n";

// ── Create resources in parallel ───────────────────────────────
$ledger = $async->ledgers->create('Async Ledger', ['source' => 'async'])->wait();
$ledgerId = $ledger['ledger_id'];

// Fire balance + account creation concurrently
$results = Promises::all([
    'balance' => $async->balances->create([
        'ledger_id' => $ledgerId,
        'currency'  => 'USD',
    ]),
    'identity' => $async->identities->create([
        'identity_type' => 'individual',
        'first_name'    => 'Async',
        'last_name'     => 'User',
        'country'       => 'US',
    ]),
])->wait();

echo $results['balance']['balance_id'] . "\n";
echo $results['identity']['identity_id'] . "\n";

// ── Settle (don't fail on individual errors) ───────────────────
$settled = Promises::settle([
    'good' => $async->ledgers->all(),
    'bad'  => $async->ledgers->get('ldg_nonexistent'),
])->wait();

foreach ($settled as $key => $result) {
    echo "{$key}: {$result['state']}\n"; // 'fulfilled' or 'rejected'
    if ($result['state'] === 'fulfilled') {
        print_r($result['value']);
    }
}

// Every async resource method mirrors its sync counterpart:
$async->transactions->create([...]);        // PromiseInterface
$async->transactions->createBulk([...]);    // PromiseInterface
$async->transactions->refund('txn_xxx');    // PromiseInterface
$async->balances->createMonitor([...]);     // PromiseInterface
$async->identities->tokenize('id_xxx', [...]); // PromiseInterface
$async->reconciliation->upload('/file.csv', 'source'); // PromiseInterface
$async->search->search('transactions', [...]); // PromiseInterface
$async->search->multiSearch([...]);            // PromiseInterface
```

### Promises Utility

| Method | Description |
|--------|-------------|
| `Promises::all($promises)` | Resolves when ALL resolve. Rejects if ANY reject. |
| `Promises::settle($promises)` | Resolves when ALL settle (resolve OR reject). Never rejects. |
| `Promises::unwrap($promise)` | Synchronously resolve a single promise (`->wait()`). |
| `Promises::resolved([...])` | Create an already-resolved promise. |
| `Promises::rejected($exception)` | Create an already-rejected promise. |

Async error handling works the same as sync — HTTP errors reject with `BlnkException`:

```php
$async->ledgers->get('ldg_nonexistent')
    ->then(
        fn(array $ledger) => print_r($ledger),
        function (BlnkException $e) {
            echo "Blnk error: {$e->getMessage()}\n";
        }
    );
```

## Error Handling

All API errors throw a `Blnk\BlnkException` with the error message and HTTP status code:

```php
use Blnk\BlnkException;

try {
    $txn = $blnk->transactions->create([...]);
} catch (BlnkException $e) {
    echo "Error ({$e->getCode()}): {$e->getMessage()}";
}
```

## Testing

```bash
composer install
composer test
```

## License

This library is open-sourced software licensed under the [Apache 2.0 license](LICENSE.md).
