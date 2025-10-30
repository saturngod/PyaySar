<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\EmailService;
use App\Services\ImportExportService;

class CleanupOldFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:old-files {--days=30 : Number of days to keep files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old temporary files, PDFs, and export files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up files older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");

        $totalDeleted = 0;

        // Clean up PDF files
        $pdfDeleted = $this->cleanupPdfFiles($cutoffDate);
        $totalDeleted += $pdfDeleted;
        $this->info("✓ Deleted {$pdfDeleted} PDF files");

        // Clean up export files
        $exportDeleted = $this->cleanupExportFiles($cutoffDate);
        $totalDeleted += $exportDeleted;
        $this->info("✓ Deleted {$exportDeleted} export files");

        // Clean up temporary files
        $tempDeleted = $this->cleanupTempFiles($cutoffDate);
        $totalDeleted += $tempDeleted;
        $this->info("✓ Deleted {$tempDeleted} temporary files");

        // Clean up old audit logs (optional)
        if ($this->confirm('Do you want to clean up old audit logs?')) {
            $auditDeleted = $this->cleanupAuditLogs($cutoffDate);
            $totalDeleted += $auditDeleted;
            $this->info("✓ Deleted {$auditDeleted} audit log entries");
        }

        // Clean up old notifications (read notifications older than 90 days)
        $notificationDeleted = $this->cleanupNotifications($cutoffDate->copy()->addDays(60));
        $totalDeleted += $notificationDeleted;
        $this->info("✓ Deleted {$notificationDeleted} old notifications");

        $this->info("🎉 Cleanup completed! Total files/records deleted: {$totalDeleted}");

        return Command::SUCCESS;
    }

    /**
     * Clean up old PDF files
     */
    private function cleanupPdfFiles($cutoffDate): int
    {
        $deletedCount = 0;

        // Use EmailService to clean up PDFs
        $emailService = new EmailService();
        $deletedCount += $emailService->cleanupOldPdfs($cutoffDate->diffInDays(now()));

        return $deletedCount;
    }

    /**
     * Clean up old export files
     */
    private function cleanupExportFiles($cutoffDate): int
    {
        return ImportExportService::cleanupOldExports($cutoffDate->diffInDays(now()));
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles($cutoffDate): int
    {
        $deletedCount = 0;
        $tempPath = storage_path('app/temp');

        if (is_dir($tempPath)) {
            $files = glob($tempPath . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffDate->timestamp) {
                    if (unlink($file)) {
                        $deletedCount++;
                    }
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Clean up old audit logs
     */
    private function cleanupAuditLogs($cutoffDate): int
    {
        return \App\Models\AuditLog::where('performed_at', '<', $cutoffDate)->delete();
    }

    /**
     * Clean up old notifications
     */
    private function cleanupNotifications($cutoffDate): int
    {
        return \App\Models\Notification::where('read_at', '<', $cutoffDate)->delete();
    }
}