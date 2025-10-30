<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API version 1
Route::prefix('v1')->group(function () {
    // Public API routes
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
        ]);
    });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::get('me', [AuthController::class, 'me']);
            Route::get('tokens', [AuthController::class, 'tokens']);
            Route::delete('tokens/{tokenId}', [AuthController::class, 'revokeToken']);
            Route::post('refresh', [AuthController::class, 'refreshToken']);
        });
    });

    // Protected API routes
    Route::middleware('auth:sanctum')->group(function () {
        // Items API
        Route::apiResource('items', ItemController::class);
        Route::get('items/statistics', [ItemController::class, 'statistics']);
        Route::get('items/search', [ItemController::class, 'search']);

        // Customers API
        Route::apiResource('customers', CustomerController::class);
        Route::get('customers/statistics', [CustomerController::class, 'statistics']);
        Route::get('customers/search', [CustomerController::class, 'search']);
        Route::get('customers/{customer}/activity', [CustomerController::class, 'activity']);

        // Quotes API
        Route::apiResource('quotes', QuoteController::class);
        Route::get('quotes/statistics', [QuoteController::class, 'statistics']);
        Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus']);
        Route::post('quotes/{quote}/convert-to-invoice', [QuoteController::class, 'convertToInvoice']);
        Route::post('quotes/{quote}/send-email', [QuoteController::class, 'sendEmail']);
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'generatePdf']);

        // Invoices API
        Route::apiResource('invoices', InvoiceController::class);
        Route::get('invoices/statistics', [InvoiceController::class, 'statistics']);
        Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus']);
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid']);
        Route::post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail']);
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf']);
        Route::get('invoices/overdue', [InvoiceController::class, 'overdue']);
        Route::get('invoices/outstanding', [InvoiceController::class, 'outstanding']);

        // Dashboard API
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $stats = $reportService->generateDashboardStats(auth()->id());

                return response()->json([
                    'success' => true,
                    'data' => $stats,
                ]);
            });

            Route::get('/recent-activity', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $stats = $reportService->generateDashboardStats(auth()->id());

                return response()->json([
                    'success' => true,
                    'data' => $stats['recent_activity'],
                ]);
            });

            Route::get('/top-customers', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $stats = $reportService->generateDashboardStats(auth()->id());

                return response()->json([
                    'success' => true,
                    'data' => $stats['top_customers'],
                ]);
            });
        });

        // Reports API
        Route::prefix('reports')->group(function () {
            Route::get('/revenue', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');

                $report = $reportService->generateRevenueReport(
                    auth()->id(),
                    $startDate,
                    $endDate
                );

                return response()->json([
                    'success' => true,
                    'data' => $report,
                ]);
            });

            Route::get('/customers', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $limit = min($request->get('limit', 20), 100);

                $report = $reportService->generateCustomerReport(auth()->id(), $limit);

                return response()->json([
                    'success' => true,
                    'data' => $report,
                ]);
            });

            Route::get('/items', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');
                $limit = min($request->get('limit', 50), 100);

                $report = $reportService->generateItemSalesReport(
                    auth()->id(),
                    $startDate,
                    $endDate,
                    $limit
                );

                return response()->json([
                    'success' => true,
                    'data' => $report,
                ]);
            });

            Route::get('/outstanding', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $report = $reportService->generateOutstandingReport(auth()->id());

                return response()->json([
                    'success' => true,
                    'data' => $report,
                ]);
            });

            Route::get('/conversions', function (Request $request) {
                $reportService = new \App\Services\ReportService();
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');

                $report = $reportService->generateQuoteConversionReport(
                    auth()->id(),
                    $startDate,
                    $endDate
                );

                return response()->json([
                    'success' => true,
                    'data' => $report,
                ]);
            });
        });

        // User API
        Route::prefix('user')->group(function () {
            Route::get('/profile', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => auth()->user()->id,
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                        'created_at' => auth()->user()->created_at,
                    ],
                ]);
            });

            Route::get('/settings', function (Request $request) {
                $settings = auth()->user()->settings;

                return response()->json([
                    'success' => true,
                    'data' => $settings ? [
                        'company_name' => $settings->company_name,
                        'company_address' => $settings->company_address,
                        'company_city' => $settings->company_city,
                        'company_country' => $settings->company_country,
                        'company_postal_code' => $settings->company_postal_code,
                        'company_phone' => $settings->company_phone,
                        'company_email' => $settings->company_email,
                        'company_website' => $settings->company_website,
                        'tax_id' => $settings->tax_id,
                    ] : [],
                ]);
            });
        });
    });
});

// API Documentation redirect
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name') . ' API',
        'version' => '1.0.0',
        'description' => 'RESTful API for Invoice Management System',
        'endpoints' => [
            'health' => '/api/v1/health',
            'auth' => '/api/v1/auth',
            'items' => '/api/v1/items',
            'customers' => '/api/v1/customers',
            'quotes' => '/api/v1/quotes',
            'invoices' => '/api/v1/invoices',
            'dashboard' => '/api/v1/dashboard',
            'reports' => '/api/v1/reports',
            'user' => '/api/v1/user',
        ],
        'documentation' => '/api/v1/docs',
        'authentication' => 'Sanctum tokens required for protected routes',
    ]);
});