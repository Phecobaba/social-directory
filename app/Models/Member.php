<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone_number',
        'address',
        'photo',
        'dynamic_fields',
    ];

    protected $casts = [
        'dynamic_fields' => 'array',
    ];
}
