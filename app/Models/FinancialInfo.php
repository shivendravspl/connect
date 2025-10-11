<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialInfo extends Model
{
    use HasFactory;

    protected $table = 'financial_infos';

    protected $guarded = [];

    protected $casts = [
        'annual_turnover' => 'array',
        'net_worth' => 'decimal:2',
        'shop_area' => 'decimal:2',
        'godown_area' => 'decimal:2',
        'years_in_business' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}