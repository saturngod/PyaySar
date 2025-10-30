<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Get statistics
        $stats = [
            'total_quotes' => $user->quotes()->count(),
            'total_invoices' => $user->invoices()->count(),
            'total_customers' => $user->customers()->count(),
            'total_items' => $user->items()->count(),
            'outstanding_amount' => $user->outstanding_amount,
            'paid_amount' => $user->paid_amount,
        ];

        // Get recent quotes and invoices
        $recentQuotes = $user->quotes()
            ->with('customer')
            ->latest()
            ->take(5)
            ->get();

        $recentInvoices = $user->invoices()
            ->with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentQuotes', 'recentInvoices'));
    }
}