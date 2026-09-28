<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyAlert;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockCount;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\LedgerIntegrity;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlanBController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function dashboard(Request $request): View
    {
        $day = CarbonImmutable::now('Asia/Kuala_Lumpur')->toDateString();
        [$start, $end] = $this->period($day);
        $sales = Sale::where('status', 'completed')->where('created_at', '>=', $start)->where('created_at', '<', $end);
        if ($request->user()->role?->name === 'Cashier') {
            $sales->where('cashier_id', $request->user()->id);
        }
        $total = $sales->sum('total_amount');
        $transactions = $sales->count();
        $products = Product::withSum('inventoryMovements as balance', 'quantity')->orderBy('name')->get();
        $low = $products->filter(fn (Product $p): bool => $p->is_active && (int) $p->balance <= $p->reorder_level);
        $alerts = DiscrepancyAlert::where('status', '!=', 'RESOLVED')->count();

        return view('planb.dashboard', compact('day', 'total', 'transactions', 'low', 'alerts'));
    }

    public function products(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $products = Product::withSum('inventoryMovements as balance', 'quantity')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('sku', 'like', '%'.$search.'%')->orWhere('barcode', $search)))
            ->orderBy('name')->paginate(25)->withQueryString();

        return view('planb.products', compact('products', 'search'));
    }

    public function productForm(?Product $product = null): View
    {
        return view('planb.product-form', ['product' => $product, 'submissionKey' => (string) Str::uuid()]);
    }

    public function saveProduct(Request $request, ?Product $product = null): RedirectResponse
    {
        $this->inventory->saveProduct($request->user(), $request->all(), $product?->id);

        return redirect()->route('owner.products.index')->with('success', 'Maklumat barang disimpan. Katalog tidak menambah stok; gunakan penerimaan stok.');
    }

    public function receiptForm(): View
    {
        return view('planb.batch', ['mode' => 'receipt', 'products' => Product::orderBy('name')->get(), 'submissionKey' => (string) Str::uuid()]);
    }

    public function receive(Request $request): RedirectResponse
    {
        $id = $this->inventory->receive($request->user(), $request->all());

        return redirect()->route('owner.receipts.show', $id)->with('success', 'Semua item penerimaan disimpan bersama.');
    }

    public function receipt(int $id): View
    {
        $operation = DB::table('operations')->where('kind', 'receipt')->find($id);
        abort_unless($operation, 404);
        $movements = InventoryMovement::with(['product', 'user'])->where('reference_type', 'receipt')->where('reference_id', $id)->get();

        return view('planb.receipt', compact('operation', 'movements'));
    }

    public function saleForm(): View
    {
        return view('planb.batch', ['mode' => 'sale', 'products' => Product::where('is_active', true)->orderBy('name')->get(), 'submissionKey' => (string) Str::uuid()]);
    }

    public function sell(Request $request): RedirectResponse
    {
        $id = $this->inventory->sell($request->user(), $request->all());

        return redirect()->route('sales.show', $id)->with('success', 'Jualan disimpan. Harga dan jumlah disahkan pada server.');
    }

    public function sale(Request $request, Sale $sale): View
    {
        abort_unless($request->user()->role?->name === 'Owner' || $sale->cashier_id === $request->user()->id, 403);
        $sale->load(['items.product', 'cashier']);

        return view('planb.sale', compact('sale'));
    }

    public function withdrawalForm(): View
    {
        return view('planb.withdrawal', ['products' => Product::orderBy('name')->get(), 'submissionKey' => (string) Str::uuid()]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $this->inventory->withdraw($request->user(), $request->all());

        return redirect()->route('withdrawals.create')->with('success', 'Pengeluaran bukan jualan disimpan; tiada hasil jualan ditambah.');
    }

    public function counts(): View
    {
        return view('planb.counts', ['counts' => StockCount::with(['product', 'user'])->latest('id')->paginate(25)]);
    }

    public function countForm(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $selected = $request->query('product_id') ? Product::findOrFail($request->query('product_id')) : null;
        $token = $selected ? $this->inventory->countToken($request->user(), $selected->id) : null;

        return view('planb.count-form', ['products' => $products, 'selected' => $selected, 'token' => $token,
            'recountOf' => $request->query('recount_of'), 'submissionKey' => (string) Str::uuid()]);
    }

    public function count(Request $request): RedirectResponse
    {
        $this->inventory->count($request->user(), $request->all());

        return redirect()->route('owner.stock-counts.index')->with('success', 'Kiraan fizikal disimpan sebagai bukti baharu. Semak perbezaan jika ada.');
    }

    public function alerts(): View
    {
        return view('planb.alerts', ['alerts' => DiscrepancyAlert::with(['product', 'stockCount.user'])->latest('id')->paginate(25)]);
    }

    public function alert(DiscrepancyAlert $alert): View
    {
        $alert->load(['product', 'stockCount.user']);
        $movements = InventoryMovement::with('user')->where('product_id', $alert->product_id)->latest('id')->limit(50)->get();
        $notes = DB::table('alert_notes')->join('users', 'users.id', '=', 'alert_notes.user_id')->where('alert_id', $alert->id)
            ->orderBy('alert_notes.id')->get(['alert_notes.*', 'users.name']);

        return view('planb.alert', ['alert' => $alert, 'movements' => $movements, 'notes' => $notes,
            'balance' => $this->inventory->balance($alert->product_id), 'submissionKey' => (string) Str::uuid()]);
    }

    public function review(Request $request, DiscrepancyAlert $alert): RedirectResponse
    {
        $this->inventory->review($request->user(), $alert->id, $request->all());

        return redirect()->route('owner.alerts.show', $alert)->with('success', 'Tindakan semakan disimpan.');
    }

    public function audit(Request $request, LedgerIntegrity $integrity): View
    {
        $actionLabels = [
            'owner.dipasang' => 'Owner account created', 'akaun.log_masuk' => 'Signed in', 'akaun.log_keluar' => 'Signed out',
            'akaun.dicipta' => 'Staff member added', 'akaun.dikemas_kini' => 'Staff details updated', 'profil.dikemas_kini' => 'Profile updated',
            'barang.didaftar' => 'Item registered', 'barang.dikemas_kini' => 'Item details updated', 'stok.diterima' => 'Stock received',
            'jualan.direkod' => 'Sale recorded', 'stok.dikeluarkan' => 'Stock removed', 'stok.dikira' => 'Stock checked',
            'perbezaan.note' => 'Stock issue note added', 'perbezaan.review' => 'Stock issue under review',
            'perbezaan.close' => 'Stock issue closed', 'perbezaan.adjust' => 'Stock adjusted after review',
        ];
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', Rule::in(array_keys($actionLabels))],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);
        $filters['q'] = trim($filters['q'] ?? '');
        $itemSources = [
            'product' => ['products', 'id', 'id'],
            'sale' => ['sale_items', 'sale_id', 'product_id'],
            'receipt' => ['inventory_movements', 'reference_id', 'product_id'],
            'inventory_movement' => ['inventory_movements', 'id', 'product_id'],
            'stock_count' => ['stock_counts', 'id', 'product_id'],
            'alert' => ['discrepancy_alerts', 'id', 'product_id'],
        ];
        $query = DB::table('activity_logs')->leftJoin('users', 'users.id', '=', 'activity_logs.user_id');
        if ($filters['q'] !== '') {
            $term = $filters['q'];
            $pattern = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
            $matchingActions = array_keys(array_filter($actionLabels, fn (string $label): bool => Str::contains(Str::lower($label), Str::lower($term))));
            $query->where(function (Builder $search) use ($pattern, $matchingActions, $itemSources, $term): void {
                $search->where('users.name', 'like', $pattern)->orWhereIn('activity_logs.action', $matchingActions);
                foreach (['before_values', 'after_values'] as $values) {
                    foreach (['name', 'note', 'notes', 'transaction_no', 'sku'] as $field) {
                        $column = $search->getGrammar()->wrap('activity_logs.'.$values.'->'.$field);
                        $search->orWhereRaw('LOWER('.$column.') LIKE ?', [Str::lower($pattern)]);
                    }
                }
                foreach (['DAMAGED' => 'Damaged', 'EXPIRED' => 'Expired', 'SAMPLE' => 'Sample', 'OWN_USE' => 'Own Use'] as $reason => $label) {
                    if (Str::contains(Str::lower($label), Str::lower($term))) {
                        $search->orWhere('activity_logs.after_values->reason', $reason);
                    }
                }
                foreach ($itemSources as $type => [$table, $referenceColumn, $productColumn]) {
                    $search->orWhere(function (Builder $match) use ($type, $table, $referenceColumn, $productColumn, $pattern): void {
                        $match->where('activity_logs.reference_type', $type)->whereExists(function (Builder $items) use ($type, $table, $referenceColumn, $productColumn, $pattern): void {
                            $items->selectRaw('1')->from($table.' as source')
                                ->join('products as item', 'item.id', '=', 'source.'.$productColumn)
                                ->whereColumn('source.'.$referenceColumn, 'activity_logs.reference_id')
                                ->when($type === 'receipt', fn (Builder $receipts) => $receipts->where('source.reference_type', 'receipt'))
                                ->where('item.name', 'like', $pattern);
                        });
                    });
                }
            });
        }
        if (! empty($filters['user_id'])) {
            $query->where('activity_logs.user_id', $filters['user_id']);
        }
        if (! empty($filters['action'])) {
            $query->where('activity_logs.action', $filters['action']);
        }
        if (! empty($filters['from'])) {
            $query->where('activity_logs.created_at', '>=', $this->period($filters['from'])[0]);
        }
        if (! empty($filters['to'])) {
            $query->where('activity_logs.created_at', '<', $this->period($filters['to'])[1]);
        }
        $direction = ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';
        $activities = $query->orderBy('activity_logs.created_at', $direction)->orderBy('activity_logs.id', $direction)
            ->select('activity_logs.*', 'users.name')->paginate(30, ['*'], 'activity_page')->withQueryString();
        $movements = InventoryMovement::with(['product', 'user'])->latest('id')->paginate(30, ['*'], 'ledger_page')->withQueryString();
        $staff = User::orderBy('name')->get(['id', 'name']);
        $hasFilters = collect($filters)->except('sort')->contains(fn ($value): bool => $value !== null && $value !== '');
        $activityItems = [];
        foreach ($itemSources as $type => [$table, $referenceColumn, $productColumn]) {
            $referenceIds = $activities->getCollection()->where('reference_type', $type)->pluck('reference_id');
            if ($referenceIds->isEmpty()) {
                continue;
            }
            $items = DB::table($table.' as source')
                ->join('products as item', 'item.id', '=', 'source.'.$productColumn)
                ->whereIn('source.'.$referenceColumn, $referenceIds)
                ->when($type === 'receipt', fn ($query) => $query->where('source.reference_type', 'receipt'))
                ->get(['source.'.$referenceColumn.' as reference_id', 'item.name']);
            foreach ($items->groupBy('reference_id') as $referenceId => $names) {
                $activityItems[$type][$referenceId] = $names->pluck('name')->unique()->implode(', ');
            }
        }

        return view('planb.audit', ['activities' => $activities, 'activityItems' => $activityItems, 'movements' => $movements,
            'actionLabels' => $actionLabels, 'filters' => $filters, 'staff' => $staff, 'hasFilters' => $hasFilters, 'verification' => $integrity->verify()]);
    }

    /** @return array{CarbonImmutable,CarbonImmutable} */
    private function period(string $day): array
    {
        $start = CarbonImmutable::parse($day, 'Asia/Kuala_Lumpur')->startOfDay();

        return [$start->utc(), $start->addDay()->utc()];
    }

    public function report(Request $request): View|StreamedResponse
    {
        $data = $request->validate([
            'view' => ['nullable', 'in:daily,monthly'], 'day' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'], 'format' => ['nullable', 'in:csv'],
        ]);
        $reportView = $data['view'] ?? 'daily';
        $day = $data['day'] ?? CarbonImmutable::now('Asia/Kuala_Lumpur')->toDateString();
        $month = $data['month'] ?? substr($day, 0, 7);
        $monthStart = CarbonImmutable::parse($month.'-01', 'Asia/Kuala_Lumpur')->startOfDay();
        [$start, $end] = $this->period($day);
        if ($reportView === 'monthly') {
            [$start, $end] = [$monthStart->utc(), $monthStart->addMonth()->utc()];
        }
        $sales = Sale::with('cashier')->where('status', 'completed')->where('created_at', '>=', $start)->where('created_at', '<', $end)->orderBy('id')->get();
        $totalCents = $sales->sum(fn (Sale $sale): int => InventoryService::cents($sale->total_amount));
        $total = InventoryService::money($totalCents);
        if (($data['format'] ?? '') === 'csv') {
            return response()->streamDownload(function () use ($sales, $total): void {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['Date', 'Transaction', 'Staff Member', 'Date / Time', 'Total (RM)'], ',', '"', '');
                foreach ($sales as $sale) {
                    $cells = [$sale->created_at->timezone('Asia/Kuala_Lumpur')->toDateString(), $sale->transaction_no, $sale->cashier?->name ?? '', $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s'), $sale->total_amount];
                    $cells = array_map(fn ($value) => preg_match('/^[\s\x00-\x1f]*[=+@-]/u', (string) $value) ? "'".$value : $value, $cells);
                    fputcsv($stream, $cells, ',', '"', '');
                }
                fputcsv($stream, ['', '', '', 'Sales Total', $total], ',', '"', '');
                fclose($stream);
            }, 'jualan-'.($reportView === 'monthly' ? $month : $day).'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $average = InventoryService::money($sales->isEmpty() ? 0 : intdiv($totalCents + intdiv($sales->count(), 2), $sales->count()));
        $dailyTotals = [];
        if ($reportView === 'monthly') {
            for ($date = $monthStart; $date->lt($monthStart->addMonth()); $date = $date->addDay()) {
                $dailyTotals[$date->toDateString()] = 0;
            }
            foreach ($sales as $sale) {
                $dailyTotals[$sale->created_at->timezone('Asia/Kuala_Lumpur')->toDateString()] += InventoryService::cents($sale->total_amount);
            }
        }

        return view('planb.report', compact('day', 'month', 'reportView', 'sales', 'total', 'average', 'dailyTotals'));
    }

    public function users(): View
    {
        return view('planb.users', ['users' => User::with('role')->orderBy('name')->get()]);
    }

    public function userForm(?User $user = null): View
    {
        abort_if($user && $user->role?->name !== 'Cashier', 403);

        return view('planb.user-form', ['account' => $user]);
    }

    public function saveUser(Request $request, ?User $user = null): RedirectResponse
    {
        $this->inventory->cashier($request->user(), $request->all(), $user?->id);

        return redirect()->route('owner.users.index')->with('success', 'Akaun Cashier disimpan. Email tidak ditandakan verified secara automatik.');
    }
}
