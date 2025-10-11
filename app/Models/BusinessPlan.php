<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPlan extends Model
{
    // Enable mass assignment for these columns
    protected $fillable = [
        'application_id',
        'crop',
        'current_financial_year',
        'current_financial_year_mt',
        'current_financial_year_amount',
        'next_financial_year',
        'next_financial_year_mt',
        'next_financial_year_amount',
    ];

    // Casts for JSON/array and decimal columns
    protected $casts = [
        'current_financial_year_mt' => 'decimal:2',
        'current_financial_year_amount' => 'decimal:2',
        'next_financial_year_mt' => 'decimal:2',
        'next_financial_year_amount' => 'decimal:2',
        'yearly_targets' => 'array', // if you still plan to use it
    ];

    public $timestamps = true;

    /**
     * Relationship to Onboarding / Application
     */
    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id', 'id');
    }
}
