<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function owner(): View
    {
        return view('owner.dashboard');
    }

    public function cashier(): View
    {
        return view('cashier.dashboard');
    }
}
