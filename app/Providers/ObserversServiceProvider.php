<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Quote;
use App\Observers\CustomerObserver;
use App\Observers\InvoiceObserver;
use App\Observers\ItemObserver;
use App\Observers\QuoteObserver;
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Customer::observe(CustomerObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Item::observe(ItemObserver::class);
        Quote::observe(QuoteObserver::class);
    }
}