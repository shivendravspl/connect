<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreCategory extends Model
{
    protected $table = 'core_category';

    protected $fillable = [
        'category_name',
        'category_code',
        'numeric_code',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];
}