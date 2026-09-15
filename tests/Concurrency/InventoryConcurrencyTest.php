<?php

use App\Models\{DiscrepancyAlert, InventoryMovement, Product, Role, Sale, User};
use App\Services\{InventoryService, LedgerIntegrity};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

pest()->extend(Tests\TestCase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh', ['--force' => true])->assertExitCode(0);
});

function concurrencyOwner(): User
{
    return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'Owner'])->id, 'is_active' => true]);
}

function concurrencyStock(User $owner, int $quantity = 5): Product
{
    app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN',
        'items' => [['name' => 'Barang ujian', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 2, 'quantity' => $quantity]]]);

    return Product::orderByDesc('id')->firstOrFail();
}

/** @return list<Process> */
function concurrentWorkers(array $inputs): array
{
    $workers = [];
    DB::beginTransaction();
    DB::table('inventory_locks')->where('id', 1)->lockForUpdate()->first();
    try {
        foreach ($inputs as $input) {
            $process = new Process([PHP_BINARY, base_path('tests/Fixtures/inventory-worker.php')], base_path(), [
                'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'cashtrack_plan_b_testing',
                'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3307', 'DB_USERNAME' => 'cashtrack_plan_b_testing', 'DB_URL' => '',
            ]);
            $process->setInput(json_encode($input, JSON_THROW_ON_ERROR));
            $process->setTimeout(30);
            $process->start();
            $workers[] = $process;
        }
        $deadline = microtime(true) + 15;
        do {
            $ready = count(array_filter($workers, fn (Process $p): bool => str_contains($p->getOutput(), 'READY')));
            if ($ready === count($workers)) {
                break;
            }
            usleep(20000);
        } while (microtime(true) < $deadline);
        expect($ready)->toBe(count($workers));
    } finally {
        DB::rollBack();
    }
    foreach ($workers as $worker) {
        $worker->wait();
        expect($worker->getExitCode())->toBeIn([0, 2]);
    }

    return $workers;
}

test('two concurrent sales cannot consume the same final stock', function () {
    $owner = concurrencyOwner();
    $product = concurrencyStock($owner);
    $input = ['action' => 'sell', 'user_id' => $owner->id, 'data' => ['items' => [['product_id' => $product->id, 'quantity' => 4]]]];
    $one = $two = $input;
    $one['data']['submission_key'] = (string) Str::uuid();
    $two['data']['submission_key'] = (string) Str::uuid();
    $workers = concurrentWorkers([$one, $two]);
    expect(collect($workers)->filter(fn (Process $p): bool => $p->getExitCode() === 0)->count())->toBe(1);
    expect(Sale::count())->toBe(1);
    expect(app(InventoryService::class)->balance($product->id))->toBe(1);
    expect(app(LedgerIntegrity::class)->verify()['status'])->toBe('LULUS');
});

test('concurrent duplicate submissions return one persisted sale', function () {
    $owner = concurrencyOwner();
    $product = concurrencyStock($owner);
    $input = ['action' => 'sell', 'user_id' => $owner->id, 'data' => ['submission_key' => (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => 2]]]];
    $workers = concurrentWorkers([$input, $input]);
    expect(array_map(fn (Process $p): int => $p->getExitCode(), $workers))->toBe([0, 0]);
    expect(Sale::count())->toBe(1);
    expect(app(InventoryService::class)->balance($product->id))->toBe(3);
});

test('concurrent receipts for different products preserve one ordered hash chain', function () {
    $owner = concurrencyOwner();
    $one = concurrencyStock($owner);
    $two = concurrencyStock($owner);
    $inputs = [];
    foreach ([$one, $two] as $product) {
        $inputs[] = ['action' => 'receive', 'user_id' => $owner->id, 'data' => ['submission_key' => (string) Str::uuid(),
            'purpose' => 'STOCK_IN', 'items' => [['product_id' => $product->id, 'quantity' => 1]]]];
    }
    concurrentWorkers($inputs);
    expect(InventoryMovement::count())->toBe(4);
    expect(app(LedgerIntegrity::class)->verify()['status'])->toBe('LULUS');
});

test('concurrent adjustment requests cannot apply a discrepancy twice', function () {
    $owner = concurrencyOwner();
    $product = concurrencyStock($owner);
    $service = app(InventoryService::class);
    $service->count($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'physical_quantity' => 4, 'count_token' => $service->countToken($owner, $product->id)]);
    $alert = DiscrepancyAlert::firstOrFail();
    $inputs = [];
    foreach ([1, 2] as $i) {
        $inputs[] = ['action' => 'review', 'user_id' => $owner->id, 'alert_id' => $alert->id,
            'data' => ['submission_key' => (string) Str::uuid(), 'action' => 'adjust', 'note' => 'Semakan fizikal selesai']];
    }
    concurrentWorkers($inputs);
    expect(InventoryMovement::where('movement_type', 'ADJUSTMENT')->count())->toBe(1);
    expect($service->balance($product->id))->toBe(4);
});

test('count and sale race stores a valid snapshot or rejects the stale count', function () {
    $owner = concurrencyOwner();
    $product = concurrencyStock($owner);
    $service = app(InventoryService::class);
    concurrentWorkers([
        ['action' => 'count', 'user_id' => $owner->id, 'data' => ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id,
            'physical_quantity' => 5, 'count_token' => $service->countToken($owner, $product->id)]],
        ['action' => 'sell', 'user_id' => $owner->id, 'data' => ['submission_key' => (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => 1]]]],
    ]);
    expect($service->balance($product->id))->toBe(4);
    expect(DB::table('stock_counts')->where('expected_quantity', '!=', 5)->count())->toBe(0);
    expect(DB::table('stock_counts')->count())->toBeIn([0, 1]);
});
