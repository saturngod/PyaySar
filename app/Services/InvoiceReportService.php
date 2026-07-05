<?php

namespace App\Services;

use App\Models\InvoiceReport;
use App\Models\User;
use Illuminate\Support\Carbon;

class InvoiceReportService
{
    /**
     * Recompute and upsert the report row for a specific user + date.
     * This is always a full recompute from the invoices table — safe & idempotent.
     */
    public function recomputeForDate(User $user, Carbon $date): void
    {
        $dateStr = $date->format('Y-m-d');

        $row = $user->invoices()
            ->whereDate('open_date', $dateStr)
            ->selectRaw('
                COUNT(*) as invoice_count,
                COALESCE(SUM(total), 0) as total_amount,
                COALESCE(SUM(CASE WHEN status = ? THEN total ELSE 0 END), 0) as received_amount,
                COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as received_count
            ', ['Received', 'Received'])
            ->first();

        InvoiceReport::updateOrCreate(
            ['user_id' => $user->id, 'report_date' => $dateStr],
            [
                'total_amount' => $row->total_amount ?? 0,
                'received_amount' => $row->received_amount ?? 0,
                'invoice_count' => $row->invoice_count ?? 0,
                'received_count' => $row->received_count ?? 0,
            ]
        );
    }

    /**
     * Get aggregated report summary for the dashboard.
     *
     * NOTE: amounts are summed across all currencies. This is correct as long as
     * each user operates in a single currency. If mixed currencies are introduced
     * in future, this method must be extended to group by currency.
     *
     * @return array{
     *   daily: array{total_amount: float, received_amount: float, invoice_count: int, received_count: int},
     *   monthly: array{total_amount: float, received_amount: float, invoice_count: int, received_count: int},
     *   yearly: array{total_amount: float, received_amount: float, invoice_count: int, received_count: int},
     * }
     */
    public function getSummary(User $user): array
    {
        $today = Carbon::today();

        $startOfYear = $today->copy()->startOfYear()->format('Y-m-d');
        $startOfMonth = $today->copy()->startOfMonth()->format('Y-m-d');
        $todayStr = $today->format('Y-m-d');

        $rows = $user->invoiceReports()
            ->where('report_date', '>=', $startOfYear)
            ->get();

        // report_date is cast to date:Y-m-d — compare as formatted strings.
        $daily = $rows->filter(fn ($r) => $r->report_date->format('Y-m-d') === $todayStr);
        $monthly = $rows->filter(fn ($r) => $r->report_date->format('Y-m-d') >= $startOfMonth);
        $yearly = $rows;

        return [
            'daily' => $this->aggregate($daily),
            'monthly' => $this->aggregate($monthly),
            'yearly' => $this->aggregate($yearly),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, InvoiceReport>  $rows
     * @return array{total_amount: float, received_amount: float, invoice_count: int, received_count: int}
     */
    private function aggregate(\Illuminate\Support\Collection $rows): array
    {
        return [
            'total_amount' => (float) $rows->sum('total_amount'),
            'received_amount' => (float) $rows->sum('received_amount'),
            'invoice_count' => (int) $rows->sum('invoice_count'),
            'received_count' => (int) $rows->sum('received_count'),
        ];
    }
}
