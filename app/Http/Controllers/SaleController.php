<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SaleController extends Controller
{
    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cashier.sales.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::where('is_active', true)
            ->findOrFail($validated['product_id']);

        $quantity = $validated['quantity'];

        $unitPrice = $product->selling_price;

        $subtotal = $unitPrice * $quantity;

        DB::transaction(function () use (
            $product,
            $quantity,
            $unitPrice,
            $subtotal
        ) {
            $sale = Sale::create([
                'transaction_no' => 'TRX-' . strtoupper(Str::random(10)),
                'cashier_id' => auth()->id(),
                'total_amount' => $subtotal,
                'status' => 'completed',
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'movement_type' => 'SALE',
                'quantity' => -$quantity,
                'quantity_before' => null,
                'quantity_after' => null,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'notes' => 'Inventory movement generated from sale.',
            ]);
        });

        return redirect()
            ->route('cashier.sales.create')
            ->with('success', 'Sale recorded successfully.');
    }
}