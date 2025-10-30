<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'item_id',
        'price',
        'qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'integer',
    ];

    /**
     * Get the quote that owns the quote item.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get the item for this quote item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the total price for this quote item (price * quantity).
     */
    public function getTotalAttribute(): float
    {
        return $this->price * $this->qty;
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($quoteItem) {
            // Recalculate quote totals when quote items change
            if ($quoteItem->quote) {
                $quoteItem->quote->calculateTotals();
                $quoteItem->quote->saveQuietly();
            }
        });

        static::deleted(function ($quoteItem) {
            // Recalculate quote totals when quote items are deleted
            if ($quoteItem->quote) {
                $quoteItem->quote->calculateTotals();
                $quoteItem->quote->saveQuietly();
            }
        });
    }
}