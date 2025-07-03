<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreDistrict extends Model
{
    protected $table = 'core_district';
    public $timestamps = false;

    protected $fillable = [
        'state_id',
        'district_name',
        'district_code',
        'numeric_code',
        'effective_date',
        'is_active',
    ];

    public function state()
    {
        return $this->belongsTo(CoreState::class, 'state_id');
    }
}
