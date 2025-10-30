<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_logo',
        'company_name',
        'company_address',
        'company_email',
        'currency',
        'default_terms',
        'default_notes',
        'pdf_settings',
    ];

    protected $casts = [
        'pdf_settings' => 'array',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the default currency or fallback to USD.
     */
    public function getDefaultCurrencyAttribute(): string
    {
        return $this->currency ?? 'USD';
    }

    /**
     * Get default terms or fallback to standard terms.
     */
    public function getDefaultTermsTextAttribute(): string
    {
        return $this->default_terms ?? 'Payment is due within 30 days of receipt of invoice.';
    }

    /**
     * Get default notes or fallback to empty string.
     */
    public function getDefaultNotesTextAttribute(): string
    {
        return $this->default_notes ?? '';
    }

    /**
     * Get PDF settings with defaults.
     */
    public function getPdfSettingsWithDefaultsAttribute(): array
    {
        return array_merge([
            'font_size' => 12,
            'font_family' => 'Arial',
            'margin_top' => 20,
            'margin_right' => 20,
            'margin_bottom' => 20,
            'margin_left' => 20,
            'show_logo' => true,
            'show_company_details' => true,
            'show_item_description' => true,
        ], $this->pdf_settings ?? []);
    }

    /**
     * Get company name or fallback to user name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?? $this->user->name;
    }

    /**
     * Get company email or fallback to user email.
     */
    public function getDisplayEmailAttribute(): string
    {
        return $this->company_email ?? $this->user->email;
    }
}