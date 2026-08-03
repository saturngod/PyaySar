<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('items', ItemController::class);
    Route::get('/invoices/search-items', [InvoiceController::class, 'searchItems'])->name('invoices.search-items');
    Route::get('/invoices/{invoice}/json', [InvoiceController::class, 'showJson'])->name('invoices.json');
    Route::get('/invoices/{invoice}/pdf-image/{image}', [InvoiceController::class, 'pdfImage'])
        ->whereIn('image', ['avatar', 'logo'])
        ->name('invoices.pdf-image');
    Route::post('/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    Route::resource('invoices', InvoiceController::class);
    Route::put('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::get('/invoices/{invoice}/history', [InvoiceController::class, 'history'])->name('invoices.history');
});

require __DIR__.'/settings.php';
