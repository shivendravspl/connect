<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_year',
        'end_year',
        'period',
        'year_id',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_year' => 'date:Y-m-d',
        'end_year' => 'date:Y-m-d',
    ];

    /**
     * Get the active years
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the inactive years
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the business plans for this year
     */
    public function businessPlans()
    {
        return $this->hasMany(BusinessPlan::class);
    }

    /**
     * Get the formatted period (e.g., "2025-26")
     */
    public function getFormattedPeriodAttribute()
    {
        return $this->period;
    }

    /**
     * Get the display name for the year
     */
    public function getDisplayNameAttribute()
    {
        return 'FY ' . $this->period;
    }
}