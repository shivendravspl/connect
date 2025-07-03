<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreTerritory extends Model
{
    protected $table = 'core_territory';

    protected $fillable = [
        'territory_name',
        'territory_code',
        'numeric_code',
        'effective_date',
        'is_active',
        'business_type',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    
}