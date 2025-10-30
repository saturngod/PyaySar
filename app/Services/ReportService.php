<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\InvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate revenue report for a date range
     */
    public function generateRevenueReport($userId, $startDate = null, $endDate = null): array
    {
        $query = auth()->user()->invoices()
            ->selectRaw('
                DATE_FORMAT(date, "%Y-%m") as month,
                COUNT(*) as invoice_count,
                SUM(total) as revenue,
                SUM(tax_amount) as tax_amount,
                SUM(discount_amount) as discount_amount,
                SUM(total - tax_amount + discount_amount) as net_revenue
            ')
            ->where('status', 'paid');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $data = $query->groupBy('month')
            ->orderBy('month')
            ->get();

        $totals = [
            'total_revenue' => $data->sum('revenue'),
            'total_tax' => $data->sum('tax_amount'),
            'total_discount' => $data->sum('discount_amount'),
            'total_invoices' => $data->sum('invoice_count'),
        ];

        return [
            'monthly_data' => $data,
            'totals' => $totals,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ];
    }

    /**
     * Generate customer performance report
     */
    public function generateCustomerReport($userId, $limit = 20): array
    {
        $customers = auth()->user()->customers()
            ->withCount(['invoices', 'quotes'])
            ->withSum('invoices', 'total')
            ->orderByDesc('invoices_sum_total')
            ->limit($limit)
            ->get();

        $topCustomers = $customers->map(function ($customer) {
            $paidTotal = $customer->invoices()->where('status', 'paid')->sum('total');
            $unpaidTotal = $customer->invoices()->where('status', '!=', 'paid')->sum('total');

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'invoice_count' => $customer->invoices_count,
                'quote_count' => $customer->quotes_count,
                'total_revenue' => $paidTotal,
                'outstanding_amount' => $unpaidTotal,
                'average_invoice_value' => $customer->invoices_count > 0 ? $paidTotal / $customer->invoices_count : 0,
                'conversion_rate' => $customer->quotes_count > 0 ?
                    ($customer->invoices_count / $customer->quotes_count) * 100 : 0,
            ];
        });

        return [
            'top_customers' => $topCustomers,
            'summary' => [
                'total_customers' => auth()->user()->customers()->count(),
                'active_customers' => auth()->user()->customers()->has('invoices')->count(),
                'average_revenue_per_customer' => $customers->avg('invoices_sum_total') ?? 0,
            ]
        ];
    }

    /**
     * Generate item sales report
     */
    public function generateItemSalesReport($userId, $startDate = null, $endDate = null, $limit = 50): array
    {
        $query = auth()->user()->items()
            ->selectRaw('
                items.id,
                items.name,
                items.description,
                items.unit_price,
                SUM(invoice_items.quantity) as total_quantity_sold,
                SUM(invoice_items.quantity * invoice_items.unit_price) as total_revenue,
                COUNT(DISTINCT invoice_items.invoice_id) as invoice_count,
                AVG(invoice_items.unit_price) as average_sale_price
            ')
            ->join('invoice_items', 'items.id', '=', 'invoice_items.item_id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.user_id', auth()->id())
            ->where('invoices.status', 'paid');

        if ($startDate) {
            $query->whereDate('invoices.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('invoices.date', '<=', $endDate);
        }

        $items = $query->groupBy('items.id', 'items.name', 'items.description', 'items.unit_price')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        $topItems = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'base_price' => $item->unit_price,
                'total_quantity_sold' => $item->total_quantity_sold,
                'total_revenue' => $item->total_revenue,
                'invoice_count' => $item->invoice_count,
                'average_sale_price' => $item->average_sale_price,
                'price_variance' => $item->unit_price > 0 ?
                    (($item->average_sale_price - $item->unit_price) / $item->unit_price) * 100 : 0,
            ];
        });

        return [
            'top_items' => $topItems,
            'summary' => [
                'total_items_sold' => $items->sum('total_quantity_sold'),
                'total_revenue_from_items' => $items->sum('total_revenue'),
                'unique_items_sold' => $items->count(),
            ]
        ];
    }

    /**
     * Generate outstanding invoices report
     */
    public function generateOutstandingReport($userId): array
    {
        $outstandingInvoices = auth()->user()->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->with('customer')
            ->orderBy('due_date')
            ->get();

        $groupedByCustomer = $outstandingInvoices->groupBy('customer_id')
            ->map(function ($invoices, $customerId) {
                $customer = $invoices->first()->customer;
                $total = $invoices->sum('total');

                return [
                    'customer_id' => $customerId,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'invoice_count' => $invoices->count(),
                    'total_outstanding' => $total,
                    'overdue_amount' => $invoices->where('status', 'overdue')->sum('total'),
                    'invoices' => $invoices->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'date' => $invoice->date,
                            'due_date' => $invoice->due_date,
                            'total' => $invoice->total,
                            'status' => $invoice->status,
                            'days_overdue' => $invoice->due_date->diffInDays(now()) > 0 ?
                                $invoice->due_date->diffInDays(now()) : 0,
                        ];
                    })->sortBy('days_overdue')->values(),
                ];
            })->sortByDesc('total_outstanding')->values();

        $summary = [
            'total_outstanding' => $outstandingInvoices->sum('total'),
            'total_overdue' => $outstandingInvoices->where('status', 'overdue')->sum('total'),
            'total_invoices' => $outstandingInvoices->count(),
            'overdue_invoices' => $outstandingInvoices->where('status', 'overdue')->count(),
            'average_days_overdue' => $outstandingInvoices->where('status', 'overdue')
                ->avg(function ($invoice) {
                    return $invoice->due_date->diffInDays(now());
                }),
        ];

        return [
            'grouped_by_customer' => $groupedByCustomer,
            'summary' => $summary,
        ];
    }

    /**
     * Generate dashboard statistics
     */
    public function generateDashboardStats($userId): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Current month revenue
        $currentMonthRevenue = auth()->user()->invoices()
            ->where('status', 'paid')
            ->whereDate('date', '>=', $startOfMonth)
            ->sum('total');

        // Last month revenue
        $lastMonthRevenue = auth()->user()->invoices()
            ->where('status', 'paid')
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total');

        // Revenue growth
        $revenueGrowth = $lastMonthRevenue > 0 ?
            (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        // Outstanding amount
        $outstandingAmount = auth()->user()->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total');

        // Recent activity
        $recentQuotes = auth()->user()->quotes()
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'quote_number', 'title', 'status', 'total', 'customer_id', 'created_at']);

        $recentInvoices = auth()->user()->invoices()
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'invoice_number', 'title', 'status', 'total', 'customer_id', 'created_at']);

        // Top customers this month
        $topCustomers = auth()->user()->customers()
            ->selectRaw('
                customers.id,
                customers.name,
                SUM(invoices.total) as revenue
            ')
            ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
            ->where('invoices.status', 'paid')
            ->whereDate('invoices.date', '>=', $startOfMonth)
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        return [
            'stats' => [
                'current_month_revenue' => $currentMonthRevenue,
                'last_month_revenue' => $lastMonthRevenue,
                'revenue_growth' => $revenueGrowth,
                'outstanding_amount' => $outstandingAmount,
                'total_quotes' => auth()->user()->quotes()->count(),
                'total_invoices' => auth()->user()->invoices()->count(),
                'paid_invoices' => auth()->user()->invoices()->where('status', 'paid')->count(),
                'total_customers' => auth()->user()->customers()->count(),
            ],
            'recent_activity' => [
                'quotes' => $recentQuotes->map(function ($quote) {
                    return [
                        'id' => $quote->id,
                        'number' => $quote->quote_number,
                        'title' => $quote->title,
                        'status' => $quote->status,
                        'total' => $quote->total,
                        'customer_name' => $quote->customer->name,
                        'created_at' => $quote->created_at->format('M d, Y'),
                    ];
                }),
                'invoices' => $recentInvoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'number' => $invoice->invoice_number,
                        'title' => $invoice->title,
                        'status' => $invoice->status,
                        'total' => $invoice->total,
                        'customer_name' => $invoice->customer->name,
                        'created_at' => $invoice->created_at->format('M d, Y'),
                    ];
                }),
            ],
            'top_customers' => $topCustomers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'revenue' => $customer->revenue,
                ];
            }),
        ];
    }

    /**
     * Generate quote conversion report
     */
    public function generateQuoteConversionReport($userId, $startDate = null, $endDate = null): array
    {
        $query = auth()->user()->quotes()
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_quotes,
                SUM(CASE WHEN status IN ("Converted", "Seen") THEN 1 ELSE 0 END) as converted_quotes,
                SUM(CASE WHEN status = "Draft" THEN 1 ELSE 0 END) as draft_quotes,
                SUM(CASE WHEN status = "Sent" THEN 1 ELSE 0 END) as sent_quotes
            ');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $data = $query->groupBy('month')
            ->orderBy('month')
            ->get();

        $conversionData = $data->map(function ($item) {
            $conversionRate = $item->total_quotes > 0 ?
                ($item->converted_quotes / $item->total_quotes) * 100 : 0;

            return [
                'month' => $item->month,
                'total_quotes' => $item->total_quotes,
                'converted_quotes' => $item->converted_quotes,
                'draft_quotes' => $item->draft_quotes,
                'sent_quotes' => $item->sent_quotes,
                'conversion_rate' => round($conversionRate, 2),
            ];
        });

        $totals = [
            'total_quotes' => $data->sum('total_quotes'),
            'converted_quotes' => $data->sum('converted_quotes'),
            'overall_conversion_rate' => $data->sum('total_quotes') > 0 ?
                ($data->sum('converted_quotes') / $data->sum('total_quotes')) * 100 : 0,
        ];

        return [
            'monthly_data' => $conversionData,
            'totals' => $totals,
        ];
    }
}