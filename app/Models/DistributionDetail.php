<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionDetail extends Model
{
    use HasFactory;

    protected $table = 'distribution_details';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'area_covered' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id', 'id');
    }
}