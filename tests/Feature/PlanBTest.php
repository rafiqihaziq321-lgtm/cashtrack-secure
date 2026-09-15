<?php

use App\Models\DiscrepancyAlert;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\LedgerIntegrity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

function planBUser(string $role = 'Owner'): User
{
    return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $role])->id, 'is_active' => true]);
}

function planBItem(array $overrides = []): array
{
    return array_replace(['name' => 'Roti coklat', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => 20], $overrides);
}

function planBReceive(User $owner, array $items): int
{
    return app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => $items]);
}

function planBCount(User $owner, Product $product, int $physical): int
{
    $service = app(InventoryService::class);

    return $service->count($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id,
        'physical_quantity' => $physical, 'count_token' => $service->countToken($owner, $product->id)]);
}

test('public registration refuses an attempted owner account', function () {
    $this->post('/register', ['email' => 'intruder@example.test', 'password' => 'NeverUseThis123!', 'role' => 'Owner'])->assertNotFound();
    $this->assertDatabaseCount('users', 0);
});

test('cashier cannot bypass owner-only receiving through a direct request', function () {
    $cashier = planBUser('Cashier');
    $this->actingAs($cashier)->post('/owner/stock-in', ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [planBItem()]])->assertForbidden();
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('inactive user cannot log in with a correct password', function () {
    $user = planBUser('Cashier');
    $user->update(['is_active' => false]);
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('an old session is rejected after account deactivation', function () {
    $user = planBUser('Cashier');
    $this->actingAs($user);
    DB::table('users')->where('id', $user->id)->update(['is_active' => false]);
    $this->post('/cashier/sales', ['submission_key' => (string) Str::uuid()])->assertRedirect(route('login'));
    $this->assertDatabaseCount('sales', 0);
});

test('owner cannot be deactivated through account management', function () {
    $owner = planBUser();
    $this->actingAs($owner)->put('/owner/users/'.$owner->id, ['name' => $owner->name, 'email' => $owner->email, 'is_active' => 0])->assertForbidden();
    expect($owner->fresh()->is_active)->toBeTrue();
});

test('owner model protects operational account deletion', function () {
    $owner = planBUser();
    expect(fn () => $owner->delete())->toThrow(ValidationException::class);
    $this->assertModelExists($owner);
});

test('owner creates only a cashier even when an owner role is submitted', function () {
    $owner = planBUser();
    $this->actingAs($owner)->post('/owner/users', ['name' => 'Cashier Demo', 'email' => 'cashier@example.test',
        'password' => 'TemporaryPass123!', 'password_confirmation' => 'TemporaryPass123!', 'is_active' => 1, 'role_id' => $owner->role_id])->assertRedirect(route('owner.users.index'));
    $account = User::where('email', 'cashier@example.test')->firstOrFail();
    expect($account->role->name)->toBe('Cashier');
    expect($account->email_verified_at)->toBeNull();
    expect(Hash::check('TemporaryPass123!', $account->password))->toBeTrue();
    expect(DB::table('activity_logs')->where('reference_id', $account->id)->value('after_values'))->not->toContain('TemporaryPass123!');
});

test('batch receipt creates separate catalog records and linked movements once', function () {
    $owner = planBUser();
    $payload = ['submission_key' => (string) Str::uuid(), 'purpose' => 'OPENING', 'items' => [planBItem(['barcode' => '95500001']), planBItem(['name' => 'Kek mini', 'unit' => 'kotak', 'quantity' => 10, 'selling_price' => '12.90'])]];
    $this->actingAs($owner)->post('/owner/stock-in', $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->post('/owner/stock-in', $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseCount('products', 2);
    $this->assertDatabaseCount('inventory_movements', 2);
    $this->assertDatabaseCount('operations', 1);
    expect(Product::pluck('sku')->unique()->count())->toBe(2);
    expect(app(LedgerIntegrity::class)->verify()['status'])->toBe('LULUS');
});

test('a late duplicate barcode rolls back every catalog item movement audit and operation', function () {
    $owner = planBUser();
    $payload = ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [planBItem(['barcode' => 'DUPLICATE']), planBItem(['barcode' => 'DUPLICATE'])]];
    $this->actingAs($owner)->post('/owner/stock-in', $payload)->assertSessionHasErrors('barcode');
    $this->assertDatabaseCount('products', 0);
    $this->assertDatabaseCount('inventory_movements', 0);
    $this->assertDatabaseCount('activity_logs', 0);
    $this->assertDatabaseCount('operations', 0);
    expect(DB::table('inventory_locks')->value('last_id'))->toBeNull();
});

test('sale ignores client prices and stores exact multi-item totals only once', function () {
    $owner = planBUser();
    $cashier = planBUser('Cashier');
    planBReceive($owner, [planBItem(), planBItem(['name' => 'Kek mini', 'selling_price' => '12.90', 'quantity' => 10])]);
    $ids = Product::orderBy('id')->pluck('id');
    $payload = ['submission_key' => (string) Str::uuid(), 'total_amount' => '0.01', 'items' => [
        ['product_id' => $ids[0], 'quantity' => 3, 'unit_price' => '0.01'], ['product_id' => $ids[1], 'quantity' => 2, 'unit_price' => '0.01']]];
    $this->actingAs($cashier)->post('/cashier/sales', $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->post('/cashier/sales', $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseCount('sales', 1);
    $this->assertDatabaseCount('sale_items', 2);
    $this->assertDatabaseHas('sales', ['total_amount' => '33.30']);
    expect(app(InventoryService::class)->balance($ids[0]))->toBe(17);
    expect(app(InventoryService::class)->balance($ids[1]))->toBe(8);
});

test('second sale item with insufficient stock rolls back the entire sale', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem(['quantity' => 5]), planBItem(['name' => 'Kek', 'quantity' => 1])]);
    $ids = Product::orderBy('id')->pluck('id');
    $this->actingAs($owner)->post('/cashier/sales', ['submission_key' => (string) Str::uuid(), 'items' => [
        ['product_id' => $ids[0], 'quantity' => 2], ['product_id' => $ids[1], 'quantity' => 2]]])->assertSessionHasErrors('items');
    $this->assertDatabaseCount('sales', 0);
    $this->assertDatabaseCount('sale_items', 0);
    $this->assertDatabaseCount('inventory_movements', 2);
    expect(app(InventoryService::class)->balance($ids[0]))->toBe(5);
});

test('discontinued product cannot be sold by direct request', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    $product = Product::firstOrFail();
    $product->update(['is_active' => false]);
    $this->actingAs($owner)->post('/cashier/sales', ['submission_key' => (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => 1]]])->assertSessionHasErrors('items');
    $this->assertDatabaseCount('sales', 0);
});

test('reusing a submission key for changed content is refused', function () {
    $owner = planBUser();
    $payload = ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [planBItem()]];
    app(InventoryService::class)->receive($owner, $payload);
    $payload['items'][0]['quantity'] = 999;
    $this->actingAs($owner)->post('/owner/stock-in', $payload)->assertSessionHasErrors('submission_key');
    $this->assertDatabaseCount('inventory_movements', 1);
});

test('damaged stock decreases balance without increasing sales', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    $product = Product::firstOrFail();
    $payload = ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'quantity' => 2, 'reason' => 'DAMAGED', 'notes' => 'Bungkusan rosak'];
    $this->actingAs($owner)->post('/withdrawals', $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->post('/withdrawals', $payload)->assertSessionHasNoErrors()->assertRedirect();
    expect(app(InventoryService::class)->balance($product->id))->toBe(18);
    $this->assertDatabaseCount('sales', 0);
    $this->assertDatabaseCount('inventory_movements', 2);
});

test('a movement after the count form was opened rejects the stale submission', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    $product = Product::firstOrFail();
    $token = app(InventoryService::class)->countToken($owner, $product->id);
    planBReceive($owner, [['product_id' => $product->id, 'quantity' => 1]]);
    $this->actingAs($owner)->post('/owner/stock-counts', ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'physical_quantity' => 19, 'count_token' => $token])->assertSessionHasErrors('count_token');
    $this->assertDatabaseCount('stock_counts', 0);
});

test('cashier cannot submit an independent count', function () {
    $this->actingAs(planBUser('Cashier'))->post('/owner/stock-counts', [])->assertForbidden();
    $this->assertDatabaseCount('stock_counts', 0);
});

test('alert review and adjustment preserve evidence and change stock only once', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem(['quantity' => 15])]);
    $product = Product::firstOrFail();
    $countId = planBCount($owner, $product, 14);
    $alert = DiscrepancyAlert::firstOrFail();
    $this->actingAs($owner)->post('/owner/alerts/'.$alert->id, ['submission_key' => (string) Str::uuid(), 'action' => 'review', 'note' => 'Semak rekod penerimaan'])->assertSessionHasNoErrors();
    $payload = ['submission_key' => (string) Str::uuid(), 'action' => 'adjust', 'note' => 'Kiraan telah disemak pada rak dan stor'];
    $this->post('/owner/alerts/'.$alert->id, $payload)->assertSessionHasNoErrors();
    $this->post('/owner/alerts/'.$alert->id, $payload)->assertSessionHasNoErrors();
    $this->post('/owner/alerts/'.$alert->id, array_replace($payload, ['submission_key' => (string) Str::uuid()]))->assertSessionHasErrors('action');
    expect(app(InventoryService::class)->balance($product->id))->toBe(14);
    expect($alert->fresh()->status)->toBe('RESOLVED');
    $this->assertDatabaseHas('stock_counts', ['id' => $countId, 'expected_quantity' => 15, 'physical_quantity' => 14]);
    expect(InventoryMovement::where('movement_type', 'ADJUSTMENT')->count())->toBe(1);
    $this->assertDatabaseCount('alert_notes', 2);
});

test('an alert may close with a reason without changing stock', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    planBCount($owner, Product::firstOrFail(), 19);
    $alert = DiscrepancyAlert::firstOrFail();
    $this->actingAs($owner)->post('/owner/alerts/'.$alert->id, ['submission_key' => (string) Str::uuid(), 'action' => 'close', 'note' => 'Kiraan tersilap, semakan baru telah dibuat'])->assertSessionHasNoErrors();
    expect($alert->fresh()->status)->toBe('RESOLVED');
    expect(InventoryMovement::where('movement_type', 'ADJUSTMENT')->count())->toBe(0);
});

test('movement after a saved count blocks adjustment of old evidence', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    $product = Product::firstOrFail();
    planBCount($owner, $product, 19);
    planBReceive($owner, [['product_id' => $product->id, 'quantity' => 5]]);
    $alert = DiscrepancyAlert::firstOrFail();
    $this->actingAs($owner)->post('/owner/alerts/'.$alert->id, ['submission_key' => (string) Str::uuid(), 'action' => 'adjust', 'note' => 'Cuba laras kiraan lama'])->assertSessionHasErrors('action');
    expect(app(InventoryService::class)->balance($product->id))->toBe(25);
    expect($alert->fresh()->status)->toBe('OPEN');
});

test('newer count supersedes old adjustment evidence without deleting it', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    $product = Product::firstOrFail();
    $first = planBCount($owner, $product, 19);
    planBCount($owner, $product, 20);
    $alert = DiscrepancyAlert::firstOrFail();
    $this->actingAs($owner)->post('/owner/alerts/'.$alert->id, ['submission_key' => (string) Str::uuid(), 'action' => 'adjust', 'note' => 'Bukti lama'])->assertSessionHasErrors('action');
    $this->assertDatabaseCount('stock_counts', 2);
    $this->assertDatabaseHas('stock_counts', ['id' => $first, 'physical_quantity' => 19]);
});

test('hash verification detects changed quantities and timestamps for version two', function (string $field, mixed $value) {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    DB::table('inventory_movements')->update([$field => $value]);
    expect(app(LedgerIntegrity::class)->verify()['status'])->toBe('GAGAL');
})->with(['quantity' => ['quantity', 999], 'timestamp' => ['created_at', '2020-01-01 00:00:00']]);

test('hash verification detects deleted tail while saved head remains', function () {
    $owner = planBUser();
    planBReceive($owner, [planBItem()]);
    DB::table('inventory_movements')->delete();
    expect(app(LedgerIntegrity::class)->verify()['head_matches'])->toBeFalse();
});

test('legacy hashes remain compatible and unsigned legacy evidence is reported', function () {
    $owner = planBUser();
    $product = Product::create(['sku' => 'OLD-1', 'name' => 'Barang lama', 'selling_price' => '1.00', 'unit' => 'unit']);
    $id = DB::table('inventory_movements')->insertGetId(['product_id' => $product->id, 'user_id' => $owner->id, 'movement_type' => 'STOCK_IN',
        'quantity' => 5, 'quantity_before' => 0, 'quantity_after' => 5, 'hash_version' => 1, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('inventory_locks')->where('id', 1)->update(['last_id' => $id]);
    expect(app(LedgerIntegrity::class)->verify()['status'])->toBe('TIDAK LENGKAP');
    $movement = InventoryMovement::findOrFail($id);
    $legacyHash = LedgerIntegrity::digest($movement);
    DB::table('inventory_movements')->where('id', $id)->update(['record_hash' => $legacyHash]);
    DB::table('inventory_locks')->where('id', 1)->update(['last_hash' => $legacyHash]);
    planBReceive($owner, [['product_id' => $product->id, 'quantity' => 1]]);
    expect(app(LedgerIntegrity::class)->verify()['status'])->toBe('LULUS');
    expect(InventoryMovement::findOrFail($id)->record_hash)->toBe($legacyHash);
});

test('daily report uses Malaysia midnight boundaries and exact money', function () {
    $owner = planBUser();
    foreach ([['2026-09-13 15:59:59', '99.00'], ['2026-09-13 16:00:00', '0.10'], ['2026-09-14 15:59:59', '0.20'], ['2026-09-14 16:00:00', '88.00']] as [$time, $amount]) {
        Sale::create(['transaction_no' => (string) Str::uuid(), 'cashier_id' => $owner->id, 'total_amount' => $amount, 'status' => 'completed'])->forceFill(['created_at' => $time])->save();
    }
    $this->actingAs($owner)->get('/owner/reports?day=2026-09-14')->assertOk()->assertViewHas('total', '0.30');
});

test('report csv neutralizes formula cells and rejects cashier access', function () {
    $owner = planBUser();
    $owner->update(['name' => '=HYPERLINK("evil")']);
    Sale::create(['transaction_no' => 'CSV-1', 'cashier_id' => $owner->id, 'total_amount' => '2.50', 'status' => 'completed']);
    $response = $this->actingAs($owner)->get('/owner/reports?format=csv');
    expect($response->streamedContent())->toContain("'=HYPERLINK");
    $this->actingAs(planBUser('Cashier'))->get('/owner/reports?format=csv')->assertForbidden();
});

test('owner pages render escaped product content and clear empty states', function (string $path) {
    $owner = planBUser();
    planBReceive($owner, [planBItem(['name' => '<script>alert(1)</script>'])]);
    $this->actingAs($owner)->get($path)->assertOk()->assertDontSee('<script>alert(1)</script>', false);
})->with(['/owner/dashboard', '/owner/products', '/owner/stock-in/create', '/cashier/sales/create', '/withdrawals/create', '/owner/stock-counts', '/owner/stock-counts/create', '/owner/alerts', '/owner/audit-trail', '/owner/reports', '/owner/users', '/owner/users/create', '/owner/products/create']);
