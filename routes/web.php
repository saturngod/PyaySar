<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BulkOperationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ImportExportController;

// Redirect root to login
Route::get("/", function () {
    return redirect()->route("login");
});

// Authentication Routes
Route::get("/login", [AuthenticatedSessionController::class, "create"])->name(
    "login",
);
Route::post("/login", [AuthenticatedSessionController::class, "store"]);
Route::post("/logout", [
    AuthenticatedSessionController::class,
    "destroy",
])->name("logout");

Route::get("/register", [RegisteredUserController::class, "create"])->name(
    "register",
);
Route::post("/register", [RegisteredUserController::class, "store"]);

// Protected Routes
Route::middleware(["auth"])->group(function () {
    // Dashboard
    Route::get("/dashboard", [DashboardController::class, "index"])->name(
        "dashboard",
    );

    // Items
    Route::resource("items", ItemController::class);
    Route::get("items/search", [ItemController::class, "search"])->name(
        "items.search",
    );

    // Customers
    Route::resource("customers", CustomerController::class);
    Route::get("customers/search", [CustomerController::class, "search"])->name(
        "customers.search",
    );

    // Quotes
    Route::resource("quotes", QuoteController::class);
    Route::post("quotes/{quote}/mark-sent", [
        QuoteController::class,
        "markAsSent",
    ])->name("quotes.mark-sent");
    Route::post("quotes/{quote}/mark-seen", [
        QuoteController::class,
        "markAsSeen",
    ])->name("quotes.mark-seen");
    Route::post("quotes/{quote}/convert-to-invoice", [
        QuoteController::class,
        "convertToInvoice",
    ])->name("quotes.convert-to-invoice");
    Route::get("quotes/{quote}/pdf", [
        QuoteController::class,
        "downloadPdf",
    ])->name("quotes.pdf");
    Route::post("quotes/{quote}/send-email", [
        QuoteController::class,
        "sendEmail",
    ])->name("quotes.send-email");
    Route::post("quotes/send-bulk-email", [
        QuoteController::class,
        "sendBulkEmails",
    ])->name("quotes.send-bulk-email");
    Route::post("quotes/{quote}/add-item", [
        QuoteController::class,
        "addItem",
    ])->name("quotes.add-item");
    Route::delete("quotes/items/{quoteItem}", [
        QuoteController::class,
        "removeItem",
    ])->name("quotes.remove-item");

    // Invoices
    Route::resource("invoices", InvoiceController::class);
    Route::get("invoices/create-from-quote/{quote}", [
        InvoiceController::class,
        "createFromQuote",
    ])->name("invoices.create-from-quote");
    Route::post("invoices/create-from-quote/{quote}", [
        InvoiceController::class,
        "storeFromQuote",
    ])->name("invoices.store-from-quote");
    Route::post("invoices/{invoice}/mark-sent", [
        InvoiceController::class,
        "markAsSent",
    ])->name("invoices.mark-sent");
    Route::post("invoices/{invoice}/mark-paid", [
        InvoiceController::class,
        "markAsPaid",
    ])->name("invoices.mark-paid");
    Route::post("invoices/{invoice}/mark-cancelled", [
        InvoiceController::class,
        "markAsCancelled",
    ])->name("invoices.mark-cancelled");
    Route::post("invoices/{invoice}/add-item", [
        InvoiceController::class,
        "addItem",
    ])->name("invoices.add-item");
    Route::delete("invoices/items/{invoiceItem}", [
        InvoiceController::class,
        "removeItem",
    ])->name("invoices.remove-item");

    // Settings
    Route::get("/settings", [SettingController::class, "edit"])->name(
        "settings.edit",
    );
    Route::put("/settings", [SettingController::class, "update"])->name(
        "settings.update",
    );
    Route::delete("/settings/logo", [
        SettingController::class,
        "removeLogo",
    ])->name("settings.remove-logo");
    Route::get("/settings/template-preview/{template}", [
        SettingController::class,
        "templatePreview",
    ])->name("settings.template-preview");
    Route::post("/settings/update-template", [
        SettingController::class,
        "updateTemplate",
    ])->name("settings.update-template");

    // Bulk Operations
    Route::post("bulk/quotes/delete", [
        BulkOperationController::class,
        "bulkDeleteQuotes",
    ])->name("bulk.quotes.delete");
    Route::post("bulk/quotes/update-status", [
        BulkOperationController::class,
        "bulkUpdateQuoteStatus",
    ])->name("bulk.quotes.update-status");
    Route::post("bulk/invoices/delete", [
        BulkOperationController::class,
        "bulkDeleteInvoices",
    ])->name("bulk.invoices.delete");
    Route::post("bulk/invoices/update-status", [
        BulkOperationController::class,
        "bulkUpdateInvoiceStatus",
    ])->name("bulk.invoices.update-status");
    Route::post("bulk/invoices/mark-paid", [
        BulkOperationController::class,
        "bulkMarkInvoicesPaid",
    ])->name("bulk.invoices.mark-paid");
    Route::post("bulk/items/delete", [
        BulkOperationController::class,
        "bulkDeleteItems",
    ])->name("bulk.items.delete");
    Route::post("bulk/customers/delete", [
        BulkOperationController::class,
        "bulkDeleteCustomers",
    ])->name("bulk.customers.delete");

    // Two-Factor Authentication
    Route::prefix("2fa")
        ->name("2fa.")
        ->group(function () {
            Route::get("/setup", [
                TwoFactorAuthController::class,
                "showSetupForm",
            ])->name("setup");
            Route::post("/setup", [
                TwoFactorAuthController::class,
                "setup",
            ])->name("setup.store");
            Route::post("/confirm", [
                TwoFactorAuthController::class,
                "confirm",
            ])->name("confirm");
            Route::get("/manage", [
                TwoFactorAuthController::class,
                "showManageForm",
            ])->name("manage");
            Route::post("/disable", [
                TwoFactorAuthController::class,
                "disable",
            ])->name("disable");
            Route::get("/verify", [
                TwoFactorAuthController::class,
                "showVerificationForm",
            ])->name("verify");
            Route::post("/verify", [
                TwoFactorAuthController::class,
                "verify",
            ])->name("verify.check");
            Route::get("/status", [
                TwoFactorAuthController::class,
                "status",
            ])->name("status");
            Route::post("/recovery-codes/regenerate", [
                TwoFactorAuthController::class,
                "regenerateRecoveryCodes",
            ])->name("recovery-codes.regenerate");
            Route::get("/backup-code", [
                TwoFactorAuthController::class,
                "showBackupCode",
            ])->name("backup-code");
        });

    // Reports
    Route::get("/reports", [ReportController::class, "index"])->name(
        "reports.index",
    );
    Route::get("/reports/dashboard-stats", [
        ReportController::class,
        "dashboardStats",
    ])->name("reports.dashboard-stats");
    Route::get("/reports/revenue", [
        ReportController::class,
        "revenueReport",
    ])->name("reports.revenue");
    Route::get("/reports/customers", [
        ReportController::class,
        "customerReport",
    ])->name("reports.customers");
    Route::get("/reports/items", [
        ReportController::class,
        "itemSalesReport",
    ])->name("reports.items");
    Route::get("/reports/outstanding", [
        ReportController::class,
        "outstandingReport",
    ])->name("reports.outstanding");
    Route::get("/reports/conversions", [
        ReportController::class,
        "quoteConversionReport",
    ])->name("reports.conversions");
    Route::post("/reports/export", [
        ReportController::class,
        "exportReport",
    ])->name("reports.export");

    // Notifications
    Route::get("/notifications", [
        NotificationController::class,
        "index",
    ])->name("notifications.index");
    Route::get("/notifications/data", [
        NotificationController::class,
        "getNotifications",
    ])->name("notifications.data");
    Route::post("/notifications/{notification}/read", [
        NotificationController::class,
        "markAsRead",
    ])->name("notifications.read");
    Route::post("/notifications/mark-read", [
        NotificationController::class,
        "markMultipleAsRead",
    ])->name("notifications.mark-multiple-read");
    Route::post("/notifications/mark-all-read", [
        NotificationController::class,
        "markAllAsRead",
    ])->name("notifications.mark-all-read");
    Route::delete("/notifications/{notification}", [
        NotificationController::class,
        "destroy",
    ])->name("notifications.destroy");
    Route::delete("/notifications/bulk-delete", [
        NotificationController::class,
        "destroyMultiple",
    ])->name("notifications.bulk-destroy");
    Route::get("/notifications/statistics", [
        NotificationController::class,
        "statistics",
    ])->name("notifications.statistics");
    Route::get("/notifications/unread-count", [
        NotificationController::class,
        "getUnreadCount",
    ])->name("notifications.unread-count");

    // Invoices - Additional routes
    Route::get("invoices/{invoice}/pdf", [
        InvoiceController::class,
        "downloadPdf",
    ])->name("invoices.pdf");
    Route::post("invoices/{invoice}/send-email", [
        InvoiceController::class,
        "sendEmail",
    ])->name("invoices.send-email");
    Route::post("invoices/send-bulk-email", [
        InvoiceController::class,
        "sendBulkEmails",
    ])->name("invoices.send-bulk-email");
    Route::post("invoices/{invoice}/mark-cancelled", [
        InvoiceController::class,
        "markAsCancelled",
    ])->name("invoices.mark-cancelled");

    // Import/Export
    Route::get("/import-export", [
        ImportExportController::class,
        "index",
    ])->name("import-export.index");
    Route::get("/export/customers", [
        ImportExportController::class,
        "exportCustomers",
    ])->name("export.customers");
    Route::get("/export/items", [
        ImportExportController::class,
        "exportItems",
    ])->name("export.items");
    Route::post("/export/quotes", [
        ImportExportController::class,
        "exportQuotes",
    ])->name("export.quotes");
    Route::post("/export/invoices", [
        ImportExportController::class,
        "exportInvoices",
    ])->name("export.invoices");
    Route::get("/import/customers", [
        ImportExportController::class,
        "showImportCustomers",
    ])->name("import.customers.show");
    Route::get("/import/items", [
        ImportExportController::class,
        "showImportItems",
    ])->name("import.items.show");
    Route::post("/import/customers", [
        ImportExportController::class,
        "importCustomers",
    ])->name("import.customers");
    Route::post("/import/items", [
        ImportExportController::class,
        "importItems",
    ])->name("import.items");
    Route::get("/template/customers", [
        ImportExportController::class,
        "downloadCustomerTemplate",
    ])->name("download.customer.template");
    Route::get("/template/items", [
        ImportExportController::class,
        "downloadItemTemplate",
    ])->name("download.item.template");
    Route::get("/import-export/statistics", [
        ImportExportController::class,
        "statistics",
    ])->name("import-export.statistics");
    Route::post("/import-export/cleanup", [
        ImportExportController::class,
        "cleanup",
    ])->name("import-export.cleanup");
});

// Test route for searchable select
Route::get("/test-searchable", function () {
    $testItems = [
        [
            "id" => 1,
            "name" => "Web Design Service",
            "unit_price" => 150.0,
            "description" => "Professional web design and development",
        ],
        [
            "id" => 2,
            "name" => "Logo Design",
            "unit_price" => 250.0,
            "description" => "Custom logo and brand identity",
        ],
        [
            "id" => 3,
            "name" => "Hosting Plan",
            "unit_price" => 20.0,
            "description" => "Monthly hosting service",
        ],
        [
            "id" => 4,
            "name" => "Consultation",
            "unit_price" => 100.0,
            "description" => "1-hour consultation session",
        ],
        [
            "id" => 5,
            "name" => "SEO Optimization",
            "unit_price" => 300.0,
            "description" => "Search engine optimization package",
        ],
    ];

    return view("test-searchable", compact("testItems"));
})->name("test.searchable");
