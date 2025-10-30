<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'quote_id',
        'title',
        'invoice_number',
        'po_number',
        'date',
        'due_date',
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
        'due_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer for this invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the quote that this invoice is based on.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get the invoice items for this invoice.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope to get invoices for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter invoices by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get invoices for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to search invoices by title or invoice number.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('invoice_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', ['Sent', 'Draft']);
    }

    /**
     * Generate a unique invoice number.
     */
    public function generateInvoiceNumber(): void
    {
        if ($this->invoice_number) {
            return;
        }

        $prefix = 'INV-' . date('Y');
        $latest = static::where('invoice_number', 'like', $prefix . '%')
                        ->where('user_id', $this->user_id)
                        ->orderBy('invoice_number', 'desc')
                        ->first();

        $number = $latest ? intval(substr($latest->invoice_number, -4)) + 1 : 1;
        $this->invoice_number = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total amounts from invoice items.
     */
    public function calculateTotals(): void
    {
        $this->sub_total = $this->invoiceItems->sum(function ($item) {
            return $item->price * $item->qty;
        });

        $this->total = max(0, $this->sub_total - $this->discount_amount);
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->status = 'Paid';
        $this->save();
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               in_array($this->status, ['Sent', 'Draft']);
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
     * Convert a quote to this invoice.
     */
    public function convertFromQuote(Quote $quote): void
    {
        $this->customer_id = $quote->customer_id;
        $this->title = str_replace('Quote', 'Invoice', $quote->title);
        $this->po_number = $quote->po_number;
        $this->date = now();
        $this->currency = $quote->currency;
        $this->terms = $quote->terms;
        $this->notes = $quote->notes;
        $this->sub_total = $quote->sub_total;
        $this->discount_amount = $quote->discount_amount;
        $this->total = $quote->total;
        $this->generateInvoiceNumber();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->generateInvoiceNumber();
            }
        });

        static::saved(function ($invoice) {
            if ($invoice->wasChanged(['sub_total', 'discount_amount'])) {
                $invoice->calculateTotals();
                if (!$invoice->wasDirty('total')) {
                    $invoice->saveQuietly();
                }
            }
        });
    }
}