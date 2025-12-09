<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_email',
        'company_address',
        'company_logo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
