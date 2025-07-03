<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreVariety extends Model
{
    protected $table = 'core_variety';

    protected $fillable = [
        'crop_id',
        'variety_name',
        'variety_code',
        'numeric_code',
        'category_id',
        'is_active',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function crop()
    {
        return $this->belongsTo(CoreCrop::class, 'crop_id');
    }

    public function category()
    {
        return $this->belongsTo(CoreCategory::class, 'category_id');
    }
}