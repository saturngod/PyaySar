<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_email',
        'company_address',
        'company_logo',
        'default_note',
        'default_bank_account_info',
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

        $disk = Storage::disk();

        if ($disk->getAdapter() instanceof AwsS3V3Adapter) {
            return $disk->temporaryUrl($this->company_logo, now()->addMinutes(30));
        }

        return Storage::url($this->company_logo);
    }
}
