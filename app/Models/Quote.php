<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'title',
        'quote_number',
        'po_number',
        'date',
        'currency',
        'status',
        'sub_total',
        'discount_amount',
        'total',
        'terms',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the quote.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer for this quote.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the quote items for this quote.
     */
    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Scope to get quotes for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter quotes by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get quotes for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to search quotes by title or quote number.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('quote_number', 'like', "%{$search}%");
        });
    }

    /**
     * Generate a unique quote number.
     */
    public function generateQuoteNumber(): void
    {
        if ($this->quote_number) {
            return;
        }

        $prefix = 'Q-' . date('Y');
        $latest = static::where('quote_number', 'like', $prefix . '%')
                        ->where('user_id', $this->user_id)
                        ->orderBy('quote_number', 'desc')
                        ->first();

        $number = $latest ? intval(substr($latest->quote_number, -4)) + 1 : 1;
        $this->quote_number = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total amounts from quote items.
     */
    public function calculateTotals(): void
    {
        $this->sub_total = $this->quoteItems->sum(function ($item) {
            return $item->price * $item->qty;
        });

        $this->total = max(0, $this->sub_total - $this->discount_amount);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    /**
     * Get total with currency symbol.
     */
    public function getTotalWithCurrencyAttribute(): string
    {
        return $this->currency . ' ' . $this->formatted_total;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (!$quote->quote_number) {
                $quote->generateQuoteNumber();
            }
        });

        static::saved(function ($quote) {
            if ($quote->wasChanged(['sub_total', 'discount_amount'])) {
                $quote->calculateTotals();
                if (!$quote->wasDirty('total')) {
                    $quote->saveQuietly();
                }
            }
        });
    }
}