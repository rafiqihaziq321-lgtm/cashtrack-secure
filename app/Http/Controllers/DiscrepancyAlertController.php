<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyAlert;
use Illuminate\View\View;

class DiscrepancyAlertController extends Controller
{
    public function index(): View
    {
        $alerts = DiscrepancyAlert::with(['product', 'stockCount'])
            ->latest()
            ->get();

        return view('owner.alerts.index', compact('alerts'));
    }
}