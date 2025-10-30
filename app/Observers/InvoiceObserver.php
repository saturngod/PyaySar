<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "creating" event.
     */
    public function creating(Invoice $invoice): void
    {
        Log::info('Creating new invoice', [
            'user_id' => $invoice->user_id,
            'customer_id' => $invoice->customer_id,
            'title' => $invoice->title,
        ]);
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        CacheService::clearInvoiceCache($invoice->user_id);

        Log::info('Invoice created', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => $invoice->total,
        ]);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        CacheService::clearInvoiceCache($invoice->user_id);

        Log::info('Invoice updated', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'total' => $invoice->total,
        ]);
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        CacheService::clearInvoiceCache($invoice->user_id);

        Log::info('Invoice deleted', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => $invoice->total,
        ]);
    }
}