<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url',
        'action_text',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Check if the notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Get the notification icon based on type.
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'quote_created' => '📄',
            'quote_sent' => '📤',
            'quote_converted' => '✅',
            'invoice_created' => '🧾',
            'invoice_sent' => '📤',
            'invoice_paid' => '💰',
            'invoice_overdue' => '⚠️',
            'customer_created' => '👤',
            'item_created' => '📦',
            'system' => '⚙️',
            'security' => '🔒',
            default => '📢',
        };
    }

    /**
     * Get the notification color based on type.
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'quote_created', 'customer_created', 'item_created' => 'blue',
            'quote_sent', 'invoice_sent' => 'green',
            'quote_converted', 'invoice_paid' => 'emerald',
            'invoice_overdue' => 'red',
            'system' => 'gray',
            'security' => 'yellow',
            default => 'blue',
        };
    }
}