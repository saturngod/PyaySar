<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\InvoiceReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackfillInvoiceReports extends Command
{
    protected $signature = 'reports:backfill {--user= : Only backfill for a specific user ID}';

    protected $description = 'Backfill invoice_reports from existing invoice data';

    public function handle(InvoiceReportService $reportService): int
    {
        $query = User::query();

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        foreach ($users as $user) {
            $this->info("Processing user #{$user->id} ({$user->email})...");

            $dates = $user->invoices()
                ->selectRaw('DISTINCT open_date')
                ->pluck('open_date');

            foreach ($dates as $date) {
                $reportService->recomputeForDate($user, Carbon::parse($date));
            }

            $this->line("  → {$dates->count()} date(s) recomputed.");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
