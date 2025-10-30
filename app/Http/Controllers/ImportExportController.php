<?php

namespace App\Http\Controllers;

use App\Services\ImportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImportExportController extends Controller
{
    /**
     * Display the import/export dashboard
     */
    public function index(): View
    {
        return view('import-export.index');
    }

    /**
     * Export customers
     */
    public function exportCustomers(): RedirectResponse
    {
        try {
            ImportExportService::ensureExportDirectory();
            $filename = ImportExportService::exportCustomers(auth()->id());

            return response()->download(storage_path("app/exports/{$filename}"))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export customers: ' . $e->getMessage());
        }
    }

    /**
     * Export items
     */
    public function exportItems(): RedirectResponse
    {
        try {
            ImportExportService::ensureExportDirectory();
            $filename = ImportExportService::exportItems(auth()->id());

            return response()->download(storage_path("app/exports/{$filename}"))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export items: ' . $e->getMessage());
        }
    }

    /**
     * Export quotes
     */
    public function exportQuotes(Request $request): RedirectResponse
    {
        try {
            ImportExportService::ensureExportDirectory();
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $filename = ImportExportService::exportQuotes(auth()->id(), $startDate, $endDate);

            return response()->download(storage_path("app/exports/{$filename}"))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export quotes: ' . $e->getMessage());
        }
    }

    /**
     * Export invoices
     */
    public function exportInvoices(Request $request): RedirectResponse
    {
        try {
            ImportExportService::ensureExportDirectory();
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $filename = ImportExportService::exportInvoices(auth()->id(), $startDate, $endDate);

            return response()->download(storage_path("app/exports/{$filename}"))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export invoices: ' . $e->getMessage());
        }
    }

    /**
     * Show import form for customers
     */
    public function showImportCustomers(): View
    {
        return view('import-export.customers-import');
    }

    /**
     * Show import form for items
     */
    public function showImportItems(): View
    {
        return view('import-export.items-import');
    }

    /**
     * Import customers from CSV
     */
    public function importCustomers(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('csv_file');
            $filePath = $file->storeAs('temp', 'customers_import_' . time() . '.csv');

            $results = ImportExportService::importCustomers(auth()->id(), storage_path("app/{$filePath}"));

            // Clean up temporary file
            unlink(storage_path("app/{$filePath}"));

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import items from CSV
     */
    public function importItems(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('csv_file');
            $filePath = $file->storeAs('temp', 'items_import_' . time() . '.csv');

            $results = ImportExportService::importItems(auth()->id(), storage_path("app/{$filePath}"));

            // Clean up temporary file
            unlink(storage_path("app/{$filePath}"));

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download customer import template
     */
    public function downloadCustomerTemplate()
    {
        try {
            ImportExportService::ensureExportDirectory();
            $filename = ImportExportService::getCustomerImportTemplate();

            return response()->download(storage_path("app/exports/{$filename}"))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Download item import template
     */
    public function downloadItemTemplate()
    {
        try {
            ImportExportService::ensureExportDirectory();
            $filename = ImportExportService::getItemImportTemplate();

            return response()->download(storage_path("app/exports/{$filename}"))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Get import/export statistics
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();

        $stats = [
            'customers_count' => $user->customers()->count(),
            'items_count' => $user->items()->count(),
            'quotes_count' => $user->quotes()->count(),
            'invoices_count' => $user->invoices()->count(),
            'total_records' => $user->customers()->count() +
                              $user->items()->count() +
                              $user->quotes()->count() +
                              $user->invoices()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Clean up old export files
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days_old' => 'required|integer|min:1|max:365',
        ]);

        try {
            $deletedCount = ImportExportService::cleanupOldExports($request->get('days_old'));

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$deletedCount} old export files.",
                'data' => ['deleted_count' => $deletedCount],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}