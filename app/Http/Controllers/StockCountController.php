<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockCount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\DiscrepancyAlert;
use Illuminate\Support\Facades\DB;

class StockCountController extends Controller
{
    public function index(): View
    {
        $stockCounts = StockCount::with(['product', 'user'])
            ->latest()
            ->get();

        return view('owner.stock_counts.index', compact('stockCounts'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('owner.stock_counts.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'physical_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = Product::where('is_active', true)
            ->findOrFail($validated['product_id']);

        $expectedQuantity = (int) InventoryMovement::where(
            'product_id',
            $product->id
        )->sum('quantity');

        $physicalQuantity = (int) $validated['physical_quantity'];

        $discrepancy = $physicalQuantity - $expectedQuantity;

        if ($discrepancy === 0) {
            $status = 'MATCH';
        } elseif ($discrepancy < 0) {
            $status = 'SHORTAGE';
        } else {
            $status = 'SURPLUS';
        }

        DB::transaction(function () use (
            $product,
            $expectedQuantity,
            $physicalQuantity,
            $discrepancy,
            $status,
            $validated
        ) {
            $stockCount = StockCount::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'expected_quantity' => $expectedQuantity,
                'physical_quantity' => $physicalQuantity,
                'discrepancy_quantity' => $discrepancy,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($status !== 'MATCH') {
                DiscrepancyAlert::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $product->id,
                    'discrepancy_quantity' => $discrepancy,
                    'type' => $status,
                    'status' => 'OPEN',
                ]);
            }
        });

        return redirect()
            ->route('owner.stock-counts.index')
            ->with('success', 'Stock count recorded successfully.');
    }
}
