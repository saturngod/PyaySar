<?php

namespace App\Http\Controllers;

use App\Services\InvoiceReportService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly InvoiceReportService $reportService,
    ) {}

    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $totalCustomers = $user->customers()->count();
        $totalInvoices = $user->invoices()->count();
        $draftInvoices = $user->invoices()
            ->where('status', 'Draft')
            ->with('customer:id,name')
            ->latest('id')
            ->take(10)
            ->get();

        $reportSummary = $this->reportService->getSummary($user);

        return Inertia::render('dashboard', [
            'totalCustomers' => $totalCustomers,
            'totalInvoices' => $totalInvoices,
            'draftInvoices' => $draftInvoices,
            'reportSummary' => $reportSummary,
        ]);
    }
}
