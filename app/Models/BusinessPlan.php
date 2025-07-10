<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPlan extends Model
{
    protected $casts = [
        'yearly_targets' => 'array'
    ];
    public $timestamps = true;

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id', 'id');
    }
    
}