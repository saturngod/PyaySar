<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_date',
        'total_amount',
        'received_amount',
        'invoice_count',
        'received_count',
    ];

    protected $casts = [
        'report_date' => 'date:Y-m-d',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
