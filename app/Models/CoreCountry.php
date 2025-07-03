<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreCountry extends Model
{
    protected $table = 'core_country';
    public $timestamps = false;

    protected $fillable = [
        'global_region',
        'country_name',
        'country_code',
        'is_active',
    ];

    public function states()
    {
        return $this->hasMany(CoreState::class, 'country_id');
    }
}
