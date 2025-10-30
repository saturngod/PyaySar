<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the reports dashboard
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Get dashboard statistics
     */
    public function dashboardStats(): JsonResponse
    {
        $stats = $this->reportService->generateDashboardStats(auth()->id());

        return response()->json($stats);
    }

    /**
     * Generate revenue report
     */
    public function revenueReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->toDateString()
            : Carbon::now()->subYear()->toDateString();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->toDateString()
            : Carbon::now()->toDateString();

        $report = $this->reportService->generateRevenueReport(
            auth()->id(),
            $startDate,
            $endDate
        );

        return response()->json($report);
    }

    /**
     * Generate customer performance report
     */
    public function customerReport(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 20), 100); // Cap at 100 for performance

        $report = $this->reportService->generateCustomerReport(auth()->id(), $limit);

        return response()->json($report);
    }

    /**
     * Generate item sales report
     */
    public function itemSalesReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->toDateString()
            : Carbon::now()->subYear()->toDateString();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->toDateString()
            : Carbon::now()->toDateString();

        $limit = min($request->get('limit', 50), 100); // Cap at 100 for performance

        $report = $this->reportService->generateItemSalesReport(
            auth()->id(),
            $startDate,
            $endDate,
            $limit
        );

        return response()->json($report);
    }

    /**
     * Generate outstanding invoices report
     */
    public function outstandingReport(): JsonResponse
    {
        $report = $this->reportService->generateOutstandingReport(auth()->id());

        return response()->json($report);
    }

    /**
     * Generate quote conversion report
     */
    public function quoteConversionReport(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->toDateString()
            : Carbon::now()->subYear()->toDateString();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))->toDateString()
            : Carbon::now()->toDateString();

        $report = $this->reportService->generateQuoteConversionReport(
            auth()->id(),
            $startDate,
            $endDate
        );

        return response()->json($report);
    }

    /**
     * Export report data as CSV
     */
    public function exportReport(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $filename = "report-{$type}-" . date('Y-m-d') . ".csv";

        $data = match($type) {
            'revenue' => $this->reportService->generateRevenueReport(
                auth()->id(),
                $request->get('start_date'),
                $request->get('end_date')
            ),
            'customers' => $this->reportService->generateCustomerReport(auth()->id()),
            'items' => $this->reportService->generateItemSalesReport(
                auth()->id(),
                $request->get('start_date'),
                $request->get('end_date')
            ),
            'outstanding' => $this->reportService->generateOutstandingReport(auth()->id()),
            'conversions' => $this->reportService->generateQuoteConversionReport(
                auth()->id(),
                $request->get('start_date'),
                $request->get('end_date')
            ),
            default => throw new \InvalidArgumentException('Invalid report type'),
        };

        // Store the CSV file temporarily
        $path = storage_path("app/temp/{$filename}");
        $this->generateCsvFile($data, $type, $path);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'path' => $path,
        ]);
    }

    /**
     * Generate CSV file for export
     */
    private function generateCsvFile($data, string $type, string $path): void
    {
        $file = fopen($path, 'w');

        switch ($type) {
            case 'revenue':
                fputcsv($file, ['Month', 'Invoice Count', 'Revenue', 'Tax Amount', 'Discount Amount', 'Net Revenue']);
                foreach ($data['monthly_data'] as $row) {
                    fputcsv($file, [
                        $row->month,
                        $row->invoice_count,
                        $row->revenue,
                        $row->tax_amount,
                        $row->discount_amount,
                        $row->net_revenue,
                    ]);
                }
                break;

            case 'customers':
                fputcsv($file, ['Customer Name', 'Email', 'Invoice Count', 'Quote Count', 'Total Revenue', 'Outstanding Amount', 'Conversion Rate']);
                foreach ($data['top_customers'] as $row) {
                    fputcsv($file, [
                        $row['name'],
                        $row['email'],
                        $row['invoice_count'],
                        $row['quote_count'],
                        $row['total_revenue'],
                        $row['outstanding_amount'],
                        round($row['conversion_rate'], 2) . '%',
                    ]);
                }
                break;

            case 'items':
                fputcsv($file, ['Item Name', 'Base Price', 'Quantity Sold', 'Total Revenue', 'Invoice Count', 'Average Sale Price', 'Price Variance']);
                foreach ($data['top_items'] as $row) {
                    fputcsv($file, [
                        $row['name'],
                        $row['base_price'],
                        $row['total_quantity_sold'],
                        $row['total_revenue'],
                        $row['invoice_count'],
                        $row['average_sale_price'],
                        round($row['price_variance'], 2) . '%',
                    ]);
                }
                break;

            case 'outstanding':
                fputcsv($file, ['Customer Name', 'Customer Email', 'Invoice Count', 'Total Outstanding', 'Overdue Amount']);
                foreach ($data['grouped_by_customer'] as $row) {
                    fputcsv($file, [
                        $row['customer_name'],
                        $row['customer_email'],
                        $row['invoice_count'],
                        $row['total_outstanding'],
                        $row['overdue_amount'],
                    ]);
                }
                break;

            case 'conversions':
                fputcsv($file, ['Month', 'Total Quotes', 'Converted Quotes', 'Draft Quotes', 'Sent Quotes', 'Conversion Rate']);
                foreach ($data['monthly_data'] as $row) {
                    fputcsv($file, [
                        $row['month'],
                        $row['total_quotes'],
                        $row['converted_quotes'],
                        $row['draft_quotes'],
                        $row['sent_quotes'],
                        $row['conversion_rate'] . '%',
                    ]);
                }
                break;
        }

        fclose($file);
    }
}