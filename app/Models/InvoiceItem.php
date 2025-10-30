<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_id',
        'price',
        'qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'integer',
    ];

    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the item for this invoice item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the total price for this invoice item (price * quantity).
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

        static::saved(function ($invoiceItem) {
            // Recalculate invoice totals when invoice items change
            if ($invoiceItem->invoice) {
                $invoiceItem->invoice->calculateTotals();
                $invoiceItem->invoice->saveQuietly();
            }
        });

        static::deleted(function ($invoiceItem) {
            // Recalculate invoice totals when invoice items are deleted
            if ($invoiceItem->invoice) {
                $invoiceItem->invoice->calculateTotals();
                $invoiceItem->invoice->saveQuietly();
            }
        });
    }
}