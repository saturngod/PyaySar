<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $totalCustomers = Auth::user()->customers()->count();
        $totalInvoices = Auth::user()->invoices()->count();
        $draftInvoices = Auth::user()->invoices()
            ->where('status', 'Draft')
            ->with('customer:id,name') // Optimize query
            ->latest('id') // Latest first
            ->take(10)
            ->get();

        return Inertia::render('dashboard', [
            'totalCustomers' => $totalCustomers,
            'totalInvoices' => $totalInvoices,
            'draftInvoices' => $draftInvoices,
        ]);
    }
}
