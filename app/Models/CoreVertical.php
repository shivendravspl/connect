<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreVertical extends Model
{
    protected $table = 'core_vertical';

    protected $fillable = [
        'vertical_name',
        'vertical_code',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];
}