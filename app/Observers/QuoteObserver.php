<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class QuoteObserver
{
    /**
     * Handle the Quote "creating" event.
     */
    public function creating(Quote $quote): void
    {
        Log::info('Creating new quote', [
            'user_id' => $quote->user_id,
            'customer_id' => $quote->customer_id,
            'title' => $quote->title,
        ]);
    }

    /**
     * Handle the Quote "created" event.
     */
    public function created(Quote $quote): void
    {
        CacheService::clearQuoteCache($quote->user_id);

        Log::info('Quote created', [
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'total' => $quote->total,
        ]);
    }

    /**
     * Handle the Quote "updated" event.
     */
    public function updated(Quote $quote): void
    {
        CacheService::clearQuoteCache($quote->user_id);

        Log::info('Quote updated', [
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'status' => $quote->status,
            'total' => $quote->total,
        ]);
    }

    /**
     * Handle the Quote "deleted" event.
     */
    public function deleted(Quote $quote): void
    {
        CacheService::clearQuoteCache($quote->user_id);

        Log::info('Quote deleted', [
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'total' => $quote->total,
        ]);
    }
}