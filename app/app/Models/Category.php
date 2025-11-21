<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status', // 'active' or 'inactive'
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];
}