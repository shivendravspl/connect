<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreRegion extends Model
{
    protected $table = 'core_region';

    protected $fillable = [
        'business_type',
        'vertical_id',
        'region_name',
        'region_code',
        'numeric_code',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function vertical()
    {
        return $this->belongsTo(\App\Models\CoreVertical::class, 'vertical_id');
    }

}