<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'contact_person',
        'contact_phone',
        'contact_email',
        'address',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quotes for this customer.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get the invoices for this customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope to get customers for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to search customers by name or contact person.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }

    /**
     * Get the total value of all quotes for this customer.
     */
    public function getTotalQuotesValueAttribute(): float
    {
        return $this->quotes()->sum('total');
    }

    /**
     * Get the total value of all invoices for this customer.
     */
    public function getTotalInvoicesValueAttribute(): float
    {
        return $this->invoices()->sum('total');
    }

    /**
     * Get the total outstanding amount for this customer.
     */
    public function getOutstandingAmountAttribute(): float
    {
        return $this->invoices()
            ->whereIn('status', ['Draft', 'Sent'])
            ->sum('total');
    }
}