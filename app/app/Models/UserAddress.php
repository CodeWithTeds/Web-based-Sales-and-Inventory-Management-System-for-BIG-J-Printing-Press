<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    protected $table = 'users_addresses';

    protected $fillable = [
        'user_id',
        'region_code',
        'province_code',
        'city_code',
        'barangay_code',
        'exact_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}