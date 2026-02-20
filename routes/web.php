<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
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
    Route::get('/invoices/search-items', [InvoiceController::class, 'searchItems'])->name('invoices.search-items');
    Route::resource('invoices', InvoiceController::class);
    Route::put('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    Route::get('/invoices/{invoice}/history', [InvoiceController::class, 'history'])->name('invoices.history');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->middleware('invoice.owner')
        ->name('invoices.pdf');

    Route::get('/ai', [AIController::class, 'index'])->name('ai.index');
    Route::post('/ai/chat', [AIController::class, 'chat'])->name('ai.chat');
});

require __DIR__.'/settings.php';
