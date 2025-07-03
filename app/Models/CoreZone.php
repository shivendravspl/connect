<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreZone extends Model
{
    protected $table = 'core_zone';

    protected $fillable = [
        'zone_name',
        'zone_code',
        'numeric_code',
        'effective_date',
        'is_active',
        'vertical_id',
        'business_type',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function vertical()
    {
        return $this->belongsTo(CoreVertical::class, 'vertical_id');
    }
}