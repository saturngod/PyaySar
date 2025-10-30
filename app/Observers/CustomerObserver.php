<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\CacheService;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        CacheService::clearCustomerCache($customer->user_id);
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        CacheService::clearCustomerCache($customer->user_id);
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        CacheService::clearCustomerCache($customer->user_id);
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        CacheService::clearCustomerCache($customer->user_id);
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        CacheService::clearCustomerCache($customer->user_id);
    }
}