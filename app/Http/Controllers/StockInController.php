<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('owner.stock_in.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = Product::where('is_active', true)
            ->findOrFail($validated['product_id']);

        $quantity = (int) $validated['quantity'];

        DB::transaction(function () use (
            $product,
            $quantity,
            $validated
        ) {
            $quantityBefore = (int) InventoryMovement::where(
                'product_id',
                $product->id
            )->sum('quantity');

            $quantityAfter = $quantityBefore + $quantity;

            InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'movement_type' => 'STOCK_IN',
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => 'stock_in',
                'reference_id' => null,
                'notes' => $validated['notes'] ?? 'Stock received.',
            ]);
        });

        return redirect()
            ->route('owner.stock-counts.create')
            ->with('success', 'Stock in recorded successfully.');
    }
}