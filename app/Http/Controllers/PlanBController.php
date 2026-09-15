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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function audit(LedgerIntegrity $integrity): View
    {
        $activities = DB::table('activity_logs')->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
            ->orderByDesc('activity_logs.id')->select('activity_logs.*', 'users.name')->paginate(30, ['*'], 'activity_page');
        $movements = InventoryMovement::with(['product', 'user'])->latest('id')->paginate(30, ['*'], 'ledger_page');

        $activityItems = [];
        $itemSources = [
            'product' => ['products', 'id', 'id'],
            'sale' => ['sale_items', 'sale_id', 'product_id'],
            'receipt' => ['inventory_movements', 'reference_id', 'product_id'],
            'inventory_movement' => ['inventory_movements', 'id', 'product_id'],
            'stock_count' => ['stock_counts', 'id', 'product_id'],
            'alert' => ['discrepancy_alerts', 'id', 'product_id'],
        ];
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

        return view('planb.audit', ['activities' => $activities, 'activityItems' => $activityItems, 'movements' => $movements, 'verification' => $integrity->verify()]);
    }

    /** @return array{CarbonImmutable,CarbonImmutable} */
    private function period(string $day): array
    {
        $start = CarbonImmutable::parse($day, 'Asia/Kuala_Lumpur')->startOfDay();

        return [$start->utc(), $start->addDay()->utc()];
    }

    public function report(Request $request): View|StreamedResponse
    {
        $data = $request->validate(['day' => ['nullable', 'date_format:Y-m-d'], 'format' => ['nullable', 'in:csv']]);
        $day = $data['day'] ?? CarbonImmutable::now('Asia/Kuala_Lumpur')->toDateString();
        [$start, $end] = $this->period($day);
        $sales = Sale::with('cashier')->where('status', 'completed')->where('created_at', '>=', $start)->where('created_at', '<', $end)->orderBy('id')->get();
        $totalCents = $sales->sum(fn (Sale $sale): int => InventoryService::cents($sale->total_amount));
        $total = InventoryService::money($totalCents);
        if (($data['format'] ?? '') === 'csv') {
            return response()->streamDownload(function () use ($sales, $day, $total): void {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['Date', 'Transaction', 'Staff Member', 'Date / Time', 'Total (RM)'], ',', '"', '');
                foreach ($sales as $sale) {
                    $cells = [$day, $sale->transaction_no, $sale->cashier?->name ?? '', $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('Y-m-d H:i:s'), $sale->total_amount];
                    $cells = array_map(fn ($value) => preg_match('/^[\s\x00-\x1f]*[=+@-]/u', (string) $value) ? "'".$value : $value, $cells);
                    fputcsv($stream, $cells, ',', '"', '');
                }
                fputcsv($stream, ['', '', '', 'Sales Total', $total], ',', '"', '');
                fclose($stream);
            }, 'jualan-'.$day.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('planb.report', compact('day', 'sales', 'total'));
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
