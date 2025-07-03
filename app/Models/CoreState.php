<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreState extends Model
{
    protected $table = 'core_state';
    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'state_name',
        'state_code',
        'short_code',
        'effective_date',
        'is_active',
    ];

    public function country()
    {
        return $this->belongsTo(CoreCountry::class, 'country_id');
    }

    public function districts()
    {
        return $this->hasMany(CoreDistrict::class, 'state_id');
    }
}
