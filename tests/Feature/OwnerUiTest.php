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

test('refreshed owner dashboard presents existing totals stock issues and quick actions', function () {
    $this->travelTo(CarbonImmutable::parse('2026-09-20 04:00:00', 'UTC'));
    $owner = ownerUiUser();
    $this->actingAs($owner);
    $inventory = app(InventoryService::class);
    $inventory->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Oat Bun', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => 3],
    ]]);
    $product = Product::firstOrFail();
    $inventory->sell($owner, ['submission_key' => (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => 1]]]);
    $inventory->count($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'physical_quantity' => 1, 'count_token' => $inventory->countToken($owner, $product->id)]);

    $response = $this->get(route('owner.dashboard'));

    $response->assertOk()->assertSee('1 transaction today')->assertSee('Oat Bun');
    $document = ownerUiDocument($response->getContent());
    expect(trim($document->query('//section[@aria-labelledby="sales-heading"]/p[1]')->item(0)->textContent))->toBe('RM 2.50');
    expect(trim($document->query('//section[@aria-labelledby="low-stock-heading"]/p[1]')->item(0)->textContent))->toBe('1');
    expect(trim($document->query('//section[@aria-labelledby="issues-heading"]/p[1]')->item(0)->textContent))->toBe('1');
    $row = $document->query('//section[@aria-labelledby="restock-heading"]//tbody/tr')->item(0);
    expect($row->textContent)->toContain('2 piece')->toContain($product->sku);
    $actions = $document->query('//section[@aria-labelledby="quick-actions-heading"]//a');
    expect(array_map(fn (DOMElement $link): string => $link->getAttribute('href'), iterator_to_array($actions)))
        ->toBe([route('cashier.sales.create'), route('owner.stock-in.create'), route('owner.stock-counts.create')]);
    expect(trim($document->query('//nav[@aria-label="Main navigation"]/a[@aria-current="page"]')->item(0)->textContent))->toBe('Dashboard');
});

test('refreshed inventory keeps search item values availability and edit destinations', function () {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Oat Bun <Fresh>', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => 12],
        ['name' => 'Mini Cake', 'unit' => 'biji', 'selling_price' => '4.00', 'reorder_level' => 3, 'quantity' => 7],
    ]]);
    $product = Product::where('name', 'Oat Bun <Fresh>')->firstOrFail();
    Product::where('name', 'Mini Cake')->update(['is_active' => false]);

    $response = $this->get(route('owner.products.index'));

    $response->assertOk()->assertSee('Oat Bun <Fresh>')->assertDontSee('<Fresh>', false)->assertSee('Available')->assertSee('Unavailable');
    $document = ownerUiDocument($response->getContent());
    $rows = $document->query('//div[@aria-label="Inventory items"]//tbody/tr');
    expect($rows->length)->toBe(2);
    expect($rows->item(1)->textContent)->toContain($product->sku)->toContain('RM 2.50')->toContain('12')->toContain('5');
    expect($document->query('//a[@aria-label="Edit Oat Bun <Fresh>"]')->item(0)->getAttribute('href'))->toBe(route('owner.products.edit', $product));
    $search = $this->get(route('owner.products.index', ['q' => 'Oat Bun']));
    $search->assertSee('Oat Bun &lt;Fresh&gt;', false)->assertDontSee('Mini Cake')->assertSee('value="Oat Bun"', false)->assertSee('Clear search');
    expect(trim(ownerUiDocument($search->getContent())->query('//nav[@aria-label="Inventory navigation"]/a[@aria-current="page"]')->item(0)->textContent))->toBe('Items & Current Stock');
});

test('refreshed owner pages explain empty inventory search and restock states', function () {
    $this->actingAs(ownerUiUser());

    $this->get(route('owner.dashboard'))->assertSee('No items are running low.');
    $this->get(route('owner.products.index'))->assertSee('Your inventory is ready for its first item.')
        ->assertSee('Use Receive Stock to add item details and the received quantity together.');
    $this->get(route('owner.products.index', ['q' => 'Missing item']))->assertSee('No items match “Missing item”.')
        ->assertSee('Try another name, Item Code or barcode.')->assertSee('Clear search');
});

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
    expect($document->query('//aside//nav[@id="owner-navigation"]/a/svg')->length)->toBe(6);
    expect($document->query('//button[@aria-controls="owner-navigation" and @aria-expanded="false"]')->length)->toBe(1);
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

test('refreshed sale keeps editable selected items and the existing submission contract', function (string $role) {
    $owner = ownerUiUser();
    app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Oat Bun <Fresh>', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 2, 'quantity' => 10],
    ]]);
    $product = Product::firstOrFail();
    $user = $role === 'Owner' ? $owner : User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $role])->id, 'is_active' => true]);
    $submissionKey = (string) Str::uuid();
    $this->actingAs($user)->withSession(['_old_input' => [
        'submission_key' => $submissionKey, 'items' => [['product_id' => $product->id, 'quantity' => 3]],
    ]]);

    $response = $this->get(route('cashier.sales.create'));

    $response->assertOk()->assertSee('Sale summary')->assertSee('Selected items')->assertSee('Complete Sale')
        ->assertSee('Oat Bun <Fresh>')->assertDontSee('<Fresh>', false);
    $document = ownerUiDocument($response->getContent());
    $form = '//form[@id="batch-form"]';
    expect($document->query($form)->item(0)->getAttribute('action'))->toBe(route('cashier.sales.store'));
    expect($document->query($form)->item(0)->getAttribute('method'))->toBe('post');
    foreach (['_token', 'submission_key', 'items[0][product_id]', 'items[0][quantity]'] as $name) {
        expect($document->query($form.'//input[@name="'.$name.'"]')->length)->toBe(1);
    }
    expect($document->query($form.'//input[@name="submission_key"]')->item(0)->getAttribute('value'))->toBe($submissionKey);
    expect($document->query('//*[@id="batch-rows"]/*[@data-row]')->length)->toBe(1);
    expect($document->query('//*[@id="batch-rows"]//*[@data-product]')->item(0)->getAttribute('value'))->toBe((string) $product->id);
    $quantity = $document->query('//*[@id="batch-rows"]//*[@data-quantity]')->item(0);
    expect([$quantity->getAttribute('value'), $quantity->getAttribute('min'), $quantity->getAttribute('max'), $quantity->hasAttribute('required')])
        ->toBe(['3', '1', '1000000', true]);
    expect($document->query($form.'//button[@type="submit"]')->length)->toBe(1);
    expect(trim($document->query($form.'//button[@type="submit"]')->item(0)->textContent))->toBe('Complete Sale');
    expect($document->query($form.'//*[@id="estimated-total" and @aria-live="polite"]')->length)->toBe(1);
    expect($document->query($form.'//button[@id="add-row" and @type="button"]')->length)->toBe(1);
    expect($document->query('//input[@id="scanner"]')->length)->toBe(1);
    expect($document->query('//template[@id="batch-template"]//*[@data-row]')->length)->toBe(1);
})->with(['Owner', 'Cashier']);

test('cashier refresh exposes only cashier navigation and preserves removal fields', function (string $route, string $active) {
    $cashier = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'Cashier'])->id, 'is_active' => true]);
    $this->actingAs($cashier);

    $response = $this->get(route($route))->assertOk();

    $document = ownerUiDocument($response->getContent());
    $links = $document->query('//nav[@aria-label="Main navigation"]/a');
    expect(array_map(fn (DOMNode $link): string => trim($link->textContent), iterator_to_array($links)))
        ->toBe(['Dashboard', 'Sales', 'Remove Stock']);
    expect(trim($document->query('//nav[@aria-label="Main navigation"]/a[@aria-current="page"]')->item(0)->textContent))->toBe($active);
    expect($document->query('//a[contains(@href, "/owner/")]')->length)->toBe(0);
    expect($document->query('//button[@aria-controls="cashier-navigation" and @aria-expanded="false"]')->length)->toBe(1);
    $account = '//details[contains(@class, "ct-account")]';
    expect($document->query($account.'//a[@href="'.route('profile.edit').'"]')->length)->toBe(1);
    expect($document->query($account.'//a[@href="'.route('security.edit').'"]')->length)->toBe(1);
    expect($document->query($account.'//form[@method="post" and @action="'.route('logout').'"]')->length)->toBe(1);
    if ($route === 'withdrawals.create') {
        $form = '//form[@method="post" and @action="'.route('withdrawals.store').'"]';
        foreach (['_token', 'submission_key', 'product_id', 'quantity', 'reason', 'notes'] as $name) {
            expect($document->query($form.'//*[@name="'.$name.'"]')->length)->toBe(1);
        }
        $reasons = $document->query($form.'//select[@name="reason"]/option');
        expect(array_map(fn (DOMElement $option): string => $option->getAttribute('value'), iterator_to_array($reasons)))
            ->toBe(['DAMAGED', 'EXPIRED', 'SAMPLE', 'OWN_USE']);
        $notes = $document->query($form.'//textarea[@name="notes"]')->item(0);
        expect([$notes->getAttribute('minlength'), $notes->getAttribute('maxlength'), $notes->hasAttribute('required')])->toBe(['3', '1000', true]);
    }
})->with([
    ['cashier.dashboard', 'Dashboard'], ['cashier.sales.create', 'Sales'], ['withdrawals.create', 'Remove Stock'],
]);

test('cashier dashboard displays only their sales and the existing low stock values', function () {
    $this->travelTo(CarbonImmutable::parse('2026-09-18 04:00:00', 'UTC'));
    $owner = ownerUiUser();
    $cashier = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'Cashier'])->id, 'is_active' => true]);
    Sale::create(['transaction_no' => 'CASHIER-UI', 'cashier_id' => $cashier->id, 'total_amount' => '7.50', 'status' => 'completed']);
    Sale::create(['transaction_no' => 'OWNER-UI', 'cashier_id' => $owner->id, 'total_amount' => '45.00', 'status' => 'completed']);
    app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Oat Bun <Fresh>', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => 3],
    ]]);

    $response = $this->actingAs($cashier)->get(route('cashier.dashboard'))->assertOk();

    $document = ownerUiDocument($response->getContent());
    expect($document->query('//section[@aria-labelledby="cashier-sales-heading"]')->item(0)->textContent)
        ->toContain('7.50')->toContain('1 transaction')->not->toContain('45.00');
    expect($document->query('//section[@aria-labelledby="cashier-low-heading"]')->item(0)->textContent)->toContain('1');
    $row = $document->query('//div[@aria-label="Items to restock"]//tbody/tr')->item(0);
    expect($row->textContent)->toContain('Oat Bun <Fresh>')->toContain('3')->toContain('5')->toContain('Let the Owner know');
    $response->assertDontSee('<Fresh>', false);
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
    expect($document->query('//details[summary[text()="Technical details"]]')->item(0)->hasAttribute('open'))->toBeFalse();
    expect($document->query('//section[@aria-label="Recent activity" and @tabindex="0"]')->length)->toBe(1);
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
    $response->assertSee('No sales on this date.')->assertSee('Choose another date to view its sales and transactions.');
    $document = ownerUiDocument($response->getContent());
    expect($document->query('//form[@aria-label="Report filters" and @method="get"]//input[@name="day"]')->item(0)->getAttribute('value'))->toBe('2026-09-15');
    expect($document->query('//a[contains(@href,"format=csv")]')->item(0)->getAttribute('href'))
        ->toBe(route('owner.reports.index', ['day' => '2026-09-15', 'format' => 'csv']));
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
    $document = ownerUiDocument($response->getContent());
    expect($document->query('//section[@aria-label="Daily sales summary"]')->item(0)->textContent)
        ->toContain(number_format($count * 2.5, 2))->toContain('18 September 2026');
    expect($document->query('//div[@aria-label="Sales transactions"]//tbody//a')->length)->toBe($count);
    foreach (Sale::all() as $sale) {
        $link = $document->query('//div[@aria-label="Sales transactions"]//a[@href="'.route('sales.show', $sale).'"]')->item(0);
        expect($link->textContent)->toBe($sale->transaction_no);
        expect($link->parentNode->parentNode->textContent)->toContain($owner->name)->toContain('12:00 pm')->toContain('2.50');
    }
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
        $response->assertOk()->assertSee('Staff details')->assertSee('Sign-in access');
        $document = ownerUiDocument($response->getContent());
        $form = $document->query('//main//form')->item(0);
        $isEdit = $url === route('owner.users.edit', $cashier);
        expect($form->getAttribute('action'))->toBe($isEdit ? route('owner.users.update', $cashier) : route('owner.users.store'));
        expect($form->getAttribute('method'))->toBe('post');
        foreach (['_token', 'name', 'email', 'password', 'password_confirmation', 'is_active'] as $field) {
            expect($document->query('//main//form//*[@name="'.$field.'"]')->length)->toBe(1);
        }
        $password = $document->query('//main//input[@name="password"]')->item(0);
        expect([$password->getAttribute('minlength'), $password->getAttribute('maxlength'), $password->hasAttribute('required')])->toBe(['12', '200', ! $isEdit]);
        if ($isEdit) {
            expect($document->query('//main//input[@name="_method"]')->item(0)->getAttribute('value'))->toBe('PUT');
            expect($document->query('//main//input[@name="email"]')->item(0)->getAttribute('value'))->toBe($cashier->email);
        }
        $options = ownerUiDocument($response->getContent())->query('//select[@name="is_active"]/option');
        expect(array_map(fn (DOMElement $option): array => [$option->getAttribute('value'), trim($option->textContent)], iterator_to_array($options)))
            ->toBe([['1', 'Enabled'], ['0', 'Disabled']]);
    }
    $this->get(route('owner.audit.index'))->assertSee('Enabled')->assertSee('Disabled')->assertDontSee('Unavailable');
    $this->view('planb.message', ['message' => 'Akaun ini dinyahaktifkan. Hubungi Owner.'])->assertSee('Your access is disabled.');
    $this->view('planb.message', ['message' => 'Akaun dikekalkan untuk sejarah audit. Owner boleh menyahaktif akaun Cashier.'])->assertSee('The Owner can disable staff access.');
});

test('welcome and account presentation preserve guest and operational navigation', function (?string $role) {
    if ($role !== null) {
        $this->actingAs(User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $role])->id, 'is_active' => true]))
            ->withSession(['auth.password_confirmed_at' => time()]);
    }

    $welcome = $this->get(route('home'))->assertOk()->assertSee('Your sales and stock,');
    $welcomeDocument = ownerUiDocument($welcome->getContent());
    expect($welcomeDocument->query('//main//a[@href="'.route($role === null ? 'login' : 'dashboard').'"]')->length)->toBe(1);
    if ($role === null) {
        expect($welcomeDocument->query('//main//a[@href="'.route('password.request').'"]')->length)->toBe(1);
        expect($welcomeDocument->query('//nav[@aria-label="Main navigation"]')->length)->toBe(0);

        return;
    }

    foreach ([['profile.edit', 'updateProfileInformation', ['name', 'email']], ['security.edit', 'updatePassword', ['current_password', 'password', 'password_confirmation']]] as [$route, $action, $fields]) {
        $response = $this->get(route($route))->assertOk()->assertSee('Your Account')->assertSee('ct-account-page');
        $response->assertSee('wire:submit="'.$action.'"', false);
        foreach ($fields as $field) {
            $response->assertSee('wire:model="'.$field.'"', false);
        }
        $document = ownerUiDocument($response->getContent());
        $navigation = $document->query('//nav[@aria-label="Main navigation"]/a');
        expect(array_map(fn (DOMNode $link): string => trim($link->textContent), iterator_to_array($navigation)))
            ->toBe($role === 'Owner' ? ['Dashboard', 'Inventory', 'Sales', 'Reports', 'Staff', 'Activity'] : ['Dashboard', 'Sales', 'Remove Stock']);
        foreach (['profile.edit', 'security.edit'] as $settingsRoute) {
            expect($document->query('//main//nav[@aria-label="Settings"]//a[@href="'.route($settingsRoute).'"]')->length)->toBe(1);
        }
        $response->assertDontSee('Repository')->assertDontSee('Documentation');
    }
})->with([null, 'Owner', 'Cashier']);

test('activity sign in and sign out remain readable with original records collapsed', function () {
    $owner = ownerUiUser();
    $inventory = app(InventoryService::class);
    foreach (['akaun.log_masuk', 'akaun.log_keluar'] as $action) {
        $inventory->audit($owner->id, $action, 'user', $owner->id, [], []);
    }
    $storedActivities = DB::table('activity_logs')->orderBy('id')->get()->toJson();

    $response = $this->actingAs($owner)->get(route('owner.audit.index'))->assertOk();

    $document = ownerUiDocument($response->getContent());
    $rows = $document->query('//section[@aria-label="Recent activity"]//tr[@class="ct-access-event"]');
    expect($rows->length)->toBe(2);
    expect($rows->item(0)->textContent)->toContain('Signed out')->toContain($owner->name);
    expect($rows->item(1)->textContent)->toContain('Signed in')->toContain($owner->name);
    $technical = '//details[summary[text()="Technical details"]]';
    expect($document->query($technical)->item(0)->hasAttribute('open'))->toBeFalse();
    expect($document->query($technical.'/details[summary[text()="Verification data and original records"]]')->item(0)->hasAttribute('open'))->toBeFalse();
    expect($document->query($technical.'//pre')->length)->toBe(2);
    expect(DB::table('activity_logs')->orderBy('id')->get()->toJson())->toBe($storedActivities);
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

test('inventory redesign keeps count removal editing and review form contracts', function () {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    $inventory = app(InventoryService::class);
    $inventory->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Oat Bun', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => 10],
    ]]);
    $product = Product::firstOrFail();
    $inventory->count($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'physical_quantity' => 9, 'notes' => 'Shelf count', 'count_token' => $inventory->countToken($owner, $product->id)]);
    $alert = DiscrepancyAlert::firstOrFail();

    foreach ([
        [route('owner.stock-counts.create', ['product_id' => $product->id]), route('owner.stock-counts.store'), ['submission_key', 'product_id', 'count_token', 'recount_of', 'physical_quantity', 'notes']],
        [route('withdrawals.create'), route('withdrawals.store'), ['submission_key', 'product_id', 'quantity', 'reason', 'notes']],
        [route('owner.products.edit', $product), route('owner.products.update', $product), ['_method', 'submission_key', 'name', 'unit', 'selling_price', 'reorder_level', 'barcode', 'description', 'is_active']],
        [route('owner.alerts.show', $alert), route('owner.alerts.review', $alert), ['submission_key', 'action', 'note']],
    ] as [$url, $action, $fields]) {
        $response = $this->get($url)->assertOk();
        $document = ownerUiDocument($response->getContent());
        $form = '//form[@method="post" and @action="'.$action.'"]';
        expect($document->query($form)->length)->toBe(1);
        expect($document->query($form.'//input[@name="_token"]')->length)->toBe(1);
        foreach ($fields as $field) {
            expect($document->query($form.'//*[@name="'.$field.'"]')->length)->toBe(1);
        }
    }
    $this->get(route('owner.stock-counts.index'))->assertSee('Oat Bun')->assertSee('Shelf count')->assertSee('Shortage')->assertSee('Count Again');
    $this->get(route('owner.alerts.index'))->assertSee('Oat Bun')->assertSee('Needs Review')->assertSee(route('owner.alerts.show', $alert));
});

test('inventory stock badges respect existing availability and low stock threshold', function (int $quantity, bool $available, bool $lowStock) {
    $owner = ownerUiUser();
    $this->actingAs($owner);
    app(InventoryService::class)->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'items' => [
        ['name' => 'Oat Bun', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => $quantity],
    ]]);
    Product::firstOrFail()->update(['is_active' => $available]);

    $response = $this->get(route('owner.products.index'));

    $row = ownerUiDocument($response->getContent())->query('//div[@aria-label="Inventory items"]//tbody/tr')->item(0);
    expect(str_contains($row->textContent, 'Low stock'))->toBe($lowStock);
    expect($row->textContent)->toContain($available ? 'Available' : 'Unavailable');
})->with([[5, true, true], [6, true, false], [5, false, false]]);
