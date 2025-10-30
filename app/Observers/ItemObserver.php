<?php

namespace App\Observers;

use App\Models\Item;
use App\Services\CacheService;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        CacheService::clearItemCache($item->user_id);
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        CacheService::clearItemCache($item->user_id);
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        CacheService::clearItemCache($item->user_id);
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        CacheService::clearItemCache($item->user_id);
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        CacheService::clearItemCache($item->user_id);
    }
}