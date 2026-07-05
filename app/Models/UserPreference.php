<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_email',
        'company_address',
        'company_logo',
    ];

    protected $appends = ['company_logo_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCompanyLogoUrlAttribute(): ?string
    {
        if (! $this->company_logo) {
            return null;
        }

        return Storage::url($this->company_logo);
    }
}
