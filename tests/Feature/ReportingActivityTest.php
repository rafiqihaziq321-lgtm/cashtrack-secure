<?php

use App\Models\DiscrepancyAlert;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\LedgerIntegrity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function reportingOwner(): User
{
    return User::factory()->create(['name' => 'Store Owner', 'role_id' => Role::firstOrCreate(['name' => 'Owner'])->id, 'is_active' => true]);
}

test('monthly report uses local month boundaries exact totals and zero filled daily values', function () {
    $owner = reportingOwner();
    foreach ([
        ['2026-08-31 15:59:59', '99.00'],
        ['2026-08-31 16:00:00', '0.10'],
        ['2026-09-01 15:59:59', '0.20'],
        ['2026-09-01 16:00:00', '2.50'],
        ['2026-09-30 15:59:59', '3.21'],
        ['2026-09-30 16:00:00', '88.00'],
    ] as $index => [$time, $amount]) {
        Sale::create(['transaction_no' => 'MONTH-'.$index, 'cashier_id' => $owner->id, 'total_amount' => $amount, 'status' => 'completed'])
            ->forceFill(['created_at' => $time])->save();
    }
    Sale::create(['transaction_no' => 'NOT-COMPLETED', 'cashier_id' => $owner->id, 'total_amount' => '77.00', 'status' => 'cancelled'])
        ->forceFill(['created_at' => '2026-09-15 00:00:00'])->save();

    $response = $this->actingAs($owner)->get(route('owner.reports.index', ['view' => 'monthly', 'month' => '2026-09']))
        ->assertOk()->assertViewHas('total', '6.01')->assertViewHas('average', '1.50')
        ->assertSee('September 2026')->assertSee('4 transactions')->assertSee('Daily sales values')
        ->assertSee('1 Sep 2026, 12:00 am')->assertSee('Store Owner')
        ->assertDontSee('MONTH-0')->assertDontSee('MONTH-5')->assertDontSee('NOT-COMPLETED');
    expect($response->viewData('sales')->pluck('transaction_no')->all())->toBe(['MONTH-1', 'MONTH-2', 'MONTH-3', 'MONTH-4']);
    $expected = array_fill_keys(array_map(fn (int $day): string => sprintf('2026-09-%02d', $day), range(1, 30)), 0);
    $expected['2026-09-01'] = 30;
    $expected['2026-09-02'] = 250;
    $expected['2026-09-30'] = 321;
    expect($response->viewData('dailyTotals'))->toBe($expected);
    $response->assertSee('1 September: RM 0.30')->assertSee('3 September: RM 0.00')->assertSee('30 September: RM 3.21');
});

test('empty monthly reports show zero summaries and every calendar day', function (string $month, int $days) {
    $response = $this->actingAs(reportingOwner())->get(route('owner.reports.index', ['view' => 'monthly', 'month' => $month]))
        ->assertOk()->assertViewHas('total', '0.00')->assertViewHas('average', '0.00')
        ->assertSee('0 transactions')->assertSee('No sales in this month.');
    expect($response->viewData('dailyTotals'))->toHaveCount($days);
    expect(array_sum($response->viewData('dailyTotals')))->toBe(0);
})->with([['2028-02', 29], ['2026-02', 28], ['2026-12', 31]]);

test('monthly csv includes local dates exact total and protects formula cells', function () {
    $owner = reportingOwner();
    $owner->update(['name' => '=SUM(1,2)']);
    Sale::create(['transaction_no' => 'MONTH-CSV', 'cashier_id' => $owner->id, 'total_amount' => '2.51', 'status' => 'completed'])
        ->forceFill(['created_at' => '2026-08-31 16:30:00'])->save();

    $response = $this->actingAs($owner)->get(route('owner.reports.index', ['view' => 'monthly', 'month' => '2026-09', 'format' => 'csv']))
        ->assertOk()->assertDownload('jualan-2026-09.csv');
    expect($response->streamedContent())->toContain('2026-09-01,MONTH-CSV', "'=SUM(1,2)", '2026-09-01 00:30:00', '2.51');
});

test('activity search matches readable fields and linked items without changing evidence', function (string $term, string $action) {
    $owner = reportingOwner();
    $this->actingAs($owner);
    $inventory = app(InventoryService::class);
    $inventory->receive($owner, ['submission_key' => (string) Str::uuid(), 'purpose' => 'STOCK_IN', 'notes' => 'Morning delivery', 'items' => [
        ['name' => 'Chocolate Bun', 'unit' => 'biji', 'selling_price' => '2.50', 'reorder_level' => 5, 'quantity' => 10],
    ]]);
    $product = Product::firstOrFail();
    $saleId = $inventory->sell($owner, ['submission_key' => (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => 1]]]);
    $inventory->withdraw($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'quantity' => 1, 'reason' => 'OWN_USE', 'notes' => 'Staff breakfast']);
    $inventory->count($owner, ['submission_key' => (string) Str::uuid(), 'product_id' => $product->id, 'physical_quantity' => 7, 'count_token' => $inventory->countToken($owner, $product->id)]);
    $inventory->review($owner, DiscrepancyAlert::firstOrFail()->id, ['submission_key' => (string) Str::uuid(), 'action' => 'review', 'note' => 'Check shelf']);
    $other = User::factory()->create();
    $inventory->audit($other->id, 'akaun.log_masuk', 'user', $other->id);
    $before = DB::table('activity_logs')->orderBy('id')->get()->toJson();
    $movements = InventoryMovement::orderBy('id')->get()->toJson();
    $verification = app(LedgerIntegrity::class)->verify();
    $search = $term === 'transaction reference' ? Sale::findOrFail($saleId)->transaction_no : $term;

    $response = $this->get(route('owner.audit.index', ['q' => $search]))->assertOk();
    $actions = $response->viewData('activities')->pluck('action')->all();
    expect($actions)->toContain($action)->not->toContain('akaun.log_masuk');
    if ($term === 'Chocolate Bun') {
        expect($actions)->toContain('barang.didaftar', 'stok.diterima', 'stok.dikeluarkan', 'stok.dikira', 'perbezaan.review');
    }
    expect(DB::table('activity_logs')->orderBy('id')->get()->toJson())->toBe($before);
    expect(InventoryMovement::orderBy('id')->get()->toJson())->toBe($movements);
    expect(app(LedgerIntegrity::class)->verify())->toBe($verification);
})->with([
    ['Store Owner', 'jualan.direkod'],
    ['Chocolate Bun', 'jualan.direkod'],
    ['stock received', 'stok.diterima'],
    ['morning delivery', 'stok.diterima'],
    ['transaction reference', 'jualan.direkod'],
    ['Own Use', 'stok.dikeluarkan'],
]);

test('activity filters combine staff type and inclusive Malaysia date range with stable sorting', function () {
    $owner = reportingOwner();
    $other = User::factory()->create();
    $inventory = app(InventoryService::class);
    foreach (['2026-09-14 15:59:59', '2026-09-14 16:00:00', '2026-09-15 15:59:59', '2026-09-15 16:00:00'] as $time) {
        $this->travelTo(CarbonImmutable::parse($time, 'UTC'));
        $inventory->audit($owner->id, 'akaun.log_masuk', 'user', $owner->id);
    }
    $this->travelTo(CarbonImmutable::parse('2026-09-15 02:00:00', 'UTC'));
    $inventory->audit($other->id, 'akaun.log_masuk', 'user', $other->id);
    $inventory->audit($owner->id, 'akaun.log_keluar', 'user', $owner->id);
    $filters = ['q' => 'Signed', 'user_id' => $owner->id, 'action' => 'akaun.log_masuk', 'from' => '2026-09-15', 'to' => '2026-09-15'];

    $newest = $this->actingAs($owner)->get(route('owner.audit.index', $filters))->assertOk();
    expect($newest->viewData('activities')->pluck('created_at')->all())->toBe(['2026-09-15 15:59:59', '2026-09-14 16:00:00']);
    $oldest = $this->get(route('owner.audit.index', [...$filters, 'sort' => 'oldest']))->assertOk();
    expect($oldest->viewData('activities')->pluck('created_at')->all())->toBe(['2026-09-14 16:00:00', '2026-09-15 15:59:59']);
});

test('activity pagination preserves filters and uses id to break timestamp ties', function () {
    $owner = reportingOwner();
    $this->travelTo(CarbonImmutable::parse('2026-09-15 02:00:00', 'UTC'));
    for ($index = 0; $index < 31; $index++) {
        app(InventoryService::class)->audit($owner->id, 'akaun.log_masuk', 'user', $owner->id);
    }
    $filters = ['q' => 'Signed in', 'user_id' => $owner->id, 'action' => 'akaun.log_masuk', 'from' => '2026-09-15', 'to' => '2026-09-15', 'sort' => 'oldest'];
    $first = $this->actingAs($owner)->get(route('owner.audit.index', $filters))->assertOk()->viewData('activities');
    expect($first->count())->toBe(30);
    parse_str(parse_url($first->nextPageUrl(), PHP_URL_QUERY), $next);
    foreach ($filters as $key => $value) {
        expect((string) $next[$key])->toBe((string) $value);
    }
    $last = $this->get($first->nextPageUrl())->assertOk()->viewData('activities');
    expect($last->count())->toBe(1);
    expect($last->first()->id)->toBeGreaterThan($first->last()->id);
});

test('activity search excludes technical payloads and treats wildcard input literally', function () {
    $owner = reportingOwner();
    app(InventoryService::class)->audit($owner->id, 'akaun.log_masuk', 'user', $owner->id, [], ['record_hash' => 'private-hash-marker']);
    foreach (['private-hash-marker', '%', '_', '0', "' OR 1=1 --", '<script>alert(1)</script>'] as $term) {
        $response = $this->actingAs($owner)->get(route('owner.audit.index', ['q' => $term]))
            ->assertOk()->assertSee('No activity matches your search.')->assertSee('Clear filters')
            ->assertDontSee('<script>alert(1)</script>', false);
        expect($response->viewData('activities')->total())->toBe(0);
    }
});

test('report and activity query controls reject invalid input', function (string $route, array $parameters, string $field) {
    $this->actingAs(reportingOwner())->getJson(route($route, $parameters))->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    ['owner.reports.index', ['month' => '2026-13', 'view' => 'monthly'], 'month'],
    ['owner.reports.index', ['view' => 'yearly'], 'view'],
    ['owner.audit.index', ['sort' => 'users.name desc'], 'sort'],
    ['owner.audit.index', ['action' => 'unknown'], 'action'],
    ['owner.audit.index', ['user_id' => 999999], 'user_id'],
    ['owner.audit.index', ['q' => ['invalid']], 'q'],
    ['owner.audit.index', ['from' => '2026-09-16', 'to' => '2026-09-15'], 'to'],
]);

test('monthly report and filtered activity remain owner only', function () {
    $cashier = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'Cashier'])->id, 'is_active' => true]);
    foreach ([route('owner.reports.index', ['view' => 'monthly']), route('owner.reports.index', ['view' => 'monthly', 'format' => 'csv']), route('owner.audit.index', ['q' => 'Stock'])] as $url) {
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($cashier)->get($url)->assertForbidden();
        auth()->logout();
    }
});
