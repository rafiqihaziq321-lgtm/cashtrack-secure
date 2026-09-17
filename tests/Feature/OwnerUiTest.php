<?php

use App\Models\DiscrepancyAlert;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\InventoryService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function ownerUiUser(): User
{
    return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'Owner'])->id, 'is_active' => true]);
}

function ownerUiDocument(string $html): DOMXPath
{
    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="UTF-8">'.$html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}

test('owner pages share business navigation without development branding', function (string $path) {
    $this->actingAs(ownerUiUser())->withSession(['auth.password_confirmed_at' => time()]);

    $response = $this->get($path);

    $response->assertOk()->assertSee('CashTrack Secure')
        ->assertDontSee('Plan B')->assertDontSee('Demo SV')->assertDontSee('demonstrasi')
        ->assertDontSee('Demonstrasi standalone')->assertDontSee('UAT')
        ->assertDontSee('Repository')->assertDontSee('Documentation');
    $document = ownerUiDocument($response->getContent());
    $links = $document->query('//nav[@aria-label="Main navigation"]/a');
    expect(array_map(fn (DOMNode $link): string => trim($link->textContent), iterator_to_array($links)))
        ->toBe(['Dashboard', 'Inventory', 'Sales', 'Reports', 'Staff', 'Activity']);
    $accountLinks = $document->query('//details[contains(@class, "ct-account")]//a');
    expect(array_map(fn (DOMNode $link): string => trim($link->textContent), iterator_to_array($accountLinks)))
        ->toBe(['Profile', 'Security']);
    expect($document->query('//details[contains(@class, "ct-account")]//form[@action="'.route('logout').'"]')->length)->toBe(1);
})->with([
    '/', '/owner/dashboard', '/owner/products', '/owner/stock-in/create', '/owner/stock-counts',
    '/owner/stock-counts/create', '/owner/alerts', '/owner/audit-trail', '/owner/reports',
    '/owner/users', '/owner/users/create', '/cashier/sales/create', '/withdrawals/create',
    '/settings/profile', '/settings/security',
]);

test('inventory groups all stock workflows and receiving keeps new item fields in the same form', function () {
    $this->actingAs(ownerUiUser());

    $response = $this->get(route('owner.stock-in.create'));

    $response->assertOk()->assertSee('New Item')->assertSee('Low Stock Alert')->assertSee('Item Code');
    $document = ownerUiDocument($response->getContent());
    $links = $document->query('//nav[@aria-label="Inventory navigation"]/a');
    expect(array_map(fn (DOMNode $link): string => trim($link->textContent), iterator_to_array($links)))
        ->toBe(['Items & Current Stock', 'Receive Stock', 'Stock Check', 'Stock Issues', 'Remove Stock']);
    foreach (['product_id', 'name', 'quantity', 'selling_price', 'barcode', 'reorder_level'] as $field) {
        expect($document->query('//form[@id="batch-form"]//input[@name="items[0]['.$field.']"]')->length)->toBe(1);
    }
    $units = $document->query('//form[@id="batch-form"]//select[@name="items[0][unit]"]/option');
    expect(array_map(fn (DOMElement $option): string => $option->getAttribute('value'), iterator_to_array($units)))
        ->toBe(['unit', 'biji', 'pek', 'kotak', 'potong']);
    expect(array_map(fn (DOMNode $option): string => trim($option->textContent), iterator_to_array($units)))
        ->toBe(['Unit', 'Piece', 'Pack', 'Box', 'Slice']);
    expect($document->query('//form[@id="batch-form"]')->item(0)->getAttribute('action'))
        ->toBe(route('owner.stock-in.store'));
});

test('standalone item creation guides the owner into receiving', function () {
    $this->actingAs(ownerUiUser());

    $response = $this->get(route('owner.products.create'));

    $response->assertOk()->assertSee('Add an Item with Receive Stock');
    $document = ownerUiDocument($response->getContent());
    expect($document->query('//main//form[@action="'.route('owner.products.store').'"]')->length)->toBe(0);
    expect($document->query('//main//a[@href="'.route('owner.stock-in.create').'"]')->length)->toBeGreaterThan(0);
});

test('activity history shows item names readable changes and reasons while preserving stored evidence', function () {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    $inventory = app(InventoryService::class);
    $item = ['name' => 'Chocolate Bun', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 2, 'quantity' => 10];
    $inventory->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [$item], 'notes' => 'Morning delivery']);
    $product = Product::firstOrFail();
    $inventory->sell($owner, ['submission_key' => (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => 1]]]);
    $inventory->count($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'physical_quantity' => 8, 'count_token' => $inventory->countToken($owner, $product->id)]);
    $inventory->review($owner, DiscrepancyAlert::firstOrFail()->id, ['submission_key' => (string) Str::uuid(), 'action' => 'review', 'note' => 'Checked the back shelf']);
    $inventory->withdraw($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'quantity' => 1, 'reason' => 'DAMAGED', 'notes' => '<script>alert("unsafe")</script>']);
    $inventory->saveProduct($owner, [...$item, 'selling_price' => '3.00', 'is_active' => false, 'submission_key' => (string) Str::uuid()], $product->id);
    $storedActivities = DB::table('activity_logs')->orderBy('id')->get()->toJson();
    $storedHashes = InventoryMovement::orderBy('id')->pluck('record_hash')->all();

    $response = $this->get(route('owner.audit.index'));

    $response->assertOk()->assertDontSee('<script>alert("unsafe")</script>', false);
    $document = ownerUiDocument($response->getContent());
    $activityTable = $document->query('//section[h2[text()="Recent Activity"]]/table')->item(0);
    $visibleText = $activityTable->textContent;
    foreach (['Stock received', 'Sale recorded', 'Stock checked', 'Stock issue under review', 'Stock removed', 'Item details updated', 'Chocolate Bun', 'Morning delivery', 'Checked the back shelf', '2.50', '3.00', 'Unavailable', 'Damaged'] as $label) {
        expect($visibleText)->toContain($label);
    }
    foreach (['reference_id', 'record_hash', 'STOCK_IN', 'DAMAGED', 'is_active', 'selling_price', 'barang.didaftar'] as $technicalValue) {
        expect($visibleText)->not->toContain($technicalValue);
    }
    foreach ($document->query('//section[h2[text()="Recent Activity"]]//tbody/tr') as $row) {
        expect($row->textContent)->toContain('Chocolate Bun');
    }
    expect($document->query('//details[summary[text()="Technical details"]]//pre')->length)->toBeGreaterThan(0);
    expect(DB::table('activity_logs')->orderBy('id')->get()->toJson())->toBe($storedActivities);
    expect(InventoryMovement::orderBy('id')->pluck('record_hash')->all())->toBe($storedHashes);
});

test('owner sees friendly stock errors and saved messages without changing validation', function () {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Chocolate Bun', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 2, 'quantity' => 1],
    ]]);

    $this->from(route('cashier.sales.create'))->post(route('cashier.sales.store'), [
        'submission_key' => (string) Str::uuid(), 'items' => [['product_id' => Product::firstOrFail()->id, 'quantity' => 2]],
    ])->assertSessionHasErrors('items');

    $validationErrors = session('errors')->getBag('default')->messages();
    $this->withSession(['success' => 'Semua item penerimaan disimpan bersama.']);
    $this->withViewErrors($validationErrors)->view('layouts.planb')
        ->assertSee('Not enough stock, or the quantity exceeds the limit: Chocolate Bun')
        ->assertSee('Stock received successfully.')->assertDontSee('Stok tidak')->assertDontSee('Semua item');
});

test('report renders business labels and keeps export and print controls', function () {
    $this->actingAs(ownerUiUser());

    $response = $this->get(route('owner.reports.index', ['day' => '2026-09-15']));

    $response->assertOk()->assertSee('Sales Total')->assertSee('Transaction')->assertSee('Export CSV')
        ->assertSee('window.print()', false)->assertDontSee('Asia/Kuala_Lumpur')
        ->assertDontSee('completed')->assertDontSee('00:00');
});

test('public account pages use the CashTrack Secure brand without demo guidance', function (string $path) {
    $response = $this->get($path);

    $response->assertOk()->assertSee('CashTrack Secure')->assertDontSee('Plan B')
        ->assertDontSee('demonstrasi')->assertDontSee('SMTP')->assertDontSee('pemasang');
})->with(['/', '/login', '/forgot-password', '/reset-password/example-token']);

test('reports use singular only for one transaction', function (int $count, string $label) {
    $this->travelTo(CarbonImmutable::parse('2026-09-18 04:00:00', 'UTC'));
    $owner = ownerUiUser();
    $this->actingAs($owner);
    for ($index = 0; $index < $count; $index++) {
        Sale::create(['transaction_no' => 'REPORT-'.$index, 'cashier_id' => $owner->id, 'total_amount' => '2.50', 'status' => 'completed']);
    }

    $response = $this->get(route('owner.reports.index', ['day' => '2026-09-18']));

    $response->assertSee('<p>'.$label.'</p>', false);
})->with([[0, '0 transactions'], [1, '1 transaction'], [2, '2 transactions']]);

test('staff access uses enabled and disabled labels while keeping boolean values', function () {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    $cashier = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'Cashier'])->id, 'is_active' => false]);
    app(InventoryService::class)->audit($owner->id, 'akaun.dikemas_kini', 'user', $cashier->id, ['is_active' => true], ['is_active' => false]);

    $this->get(route('owner.users.index'))->assertSee('Enabled')->assertSee('Disabled')
        ->assertDontSee('Available')->assertDontSee('Unavailable');
    foreach ([route('owner.users.create'), route('owner.users.edit', $cashier)] as $url) {
        $response = $this->get($url);
        $options = ownerUiDocument($response->getContent())->query('//select[@name="is_active"]/option');
        expect(array_map(fn (DOMElement $option): array => [$option->getAttribute('value'), trim($option->textContent)], iterator_to_array($options)))
            ->toBe([['1', 'Enabled'], ['0', 'Disabled']]);
    }
    $this->get(route('owner.audit.index'))->assertSee('Enabled')->assertSee('Disabled')->assertDontSee('Unavailable');
    $this->view('planb.message', ['message' => 'Akaun ini dinyahaktifkan. Hubungi Owner.'])->assertSee('Your access is disabled.');
    $this->view('planb.message', ['message' => 'Akaun dikekalkan untuk sejarah audit. Owner boleh menyahaktif akaun Cashier.'])->assertSee('The Owner can disable staff access.');
});

test('local presentation wording preserves stored notes and audit evidence', function () {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    $note = 'Data contoh SV: 20 roti dan 10 kek mini; bukan data client.';
    $receipt = app(InventoryService::class)->receive($owner, [
        'submission_key' => (string) Str::uuid(), 'purpose' => 'OPENING', 'notes' => $note,
        'items' => [['name' => 'Roti coklat demo', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 2, 'quantity' => 20]],
    ]);
    Product::firstOrFail()->update(['name' => 'Roti Coklat']);
    $movements = DB::table('inventory_movements')->orderBy('id')->get()->toJson();
    $activities = DB::table('activity_logs')->orderBy('id')->get()->toJson();
    $this->app->detectEnvironment(fn (): string => 'local');

    $receiptResponse = $this->get(route('owner.receipts.show', $receipt));
    $activityResponse = $this->get(route('owner.audit.index'));

    $receiptResponse->assertSee('Opening stock: 20 chocolate buns and 10 mini cakes.')->assertDontSee('Data contoh SV');
    $document = ownerUiDocument($activityResponse->getContent());
    foreach ($document->query('//main//table') as $table) {
        expect($table->textContent)->toContain('Opening stock: 20 chocolate buns and 10 mini cakes.')
            ->not->toContain('Data contoh SV')->not->toContain('Roti coklat demo');
    }
    expect($document->query('//details[summary[text()="Technical details"]]')->item(0)->textContent)->toContain($note);
    expect(DB::table('inventory_movements')->orderBy('id')->get()->toJson())->toBe($movements);
    expect(DB::table('activity_logs')->orderBy('id')->get()->toJson())->toBe($activities);
});

test('presentation note replacements are exact and limited to local use', function (string $environment, string $expected) {
    $this->app->detectEnvironment(fn (): string => $environment);

    $this->view('planb.message', ['message' => 'Demo SV: dua bungkusan roti rosak ketika pemeriksaan.'])->assertSee($expected);
    $this->view('planb.message', ['message' => 'Delivery note <script>alert(1)</script>'])
        ->assertSee('Delivery note <script>alert(1)</script>')->assertDontSee('<script>', false);
    $this->view('planb.message', ['message' => 'Demo SV: a different original note'])->assertSee('Demo SV: a different original note');
})->with([
    ['local', 'Two packs of chocolate buns were damaged during inspection.'],
    ['production', 'Demo SV: dua bungkusan roti rosak ketika pemeriksaan.'],
]);
