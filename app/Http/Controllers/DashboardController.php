<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\DiscrepancyAlert;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function owner()
    {
        $productCount = Product::where('is_active', true)->count();

        $expectedStock = (int) InventoryMovement::sum('quantity');

        $openAlerts = DiscrepancyAlert::where('status', 'OPEN')->count();

        $totalSales = Sale::where('status', 'completed')
            ->sum('total_amount');

        $recentAlerts = DiscrepancyAlert::with([
            'product',
            'stockCount'
        ])
            ->latest()
            ->take(5)
            ->get();

        return view('owner.dashboard', compact(
            'productCount',
            'expectedStock',
            'openAlerts',
            'totalSales',
            'recentAlerts'
        ));
    }
    public function cashier(): View
    {
        return view('cashier.dashboard');
    }
}
