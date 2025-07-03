<?php

namespace App\Models;
use App\Models\CoreVertical;
use Illuminate\Database\Eloquent\Model;

class CoreCrop extends Model
{
    protected $table = 'core_crop';

    protected $fillable = [
        'vertical_id',
        'crop_name',
        'crop_code',
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
        return $this->belongsTo(CoreVertical::class, 'vertical_id');
    }
}