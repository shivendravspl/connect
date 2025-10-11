<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Accessors for badge color based on category
    public function getBadgeColorAttribute()
    {
        $colorMap = [
            'draft' => 'secondary',
            'approval' => 'warning',
            'rejection' => 'danger',
            'mis_processing' => 'info',
            'completion' => 'success',
        ];

        return $colorMap[$this->category] ?? 'secondary';
    }

    // Get display name (human readable)
    public function getDisplayNameAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->name));
    }

    // Scope methods
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Static helper methods
    public static function getByCategory($category)
    {
        return self::active()->byCategory($category)->ordered()->get();
    }

    public static function getByName($name)
    {
        return self::active()->where('name', $name)->first();
    }

    public static function getBadgeColorForStatus($statusName)
    {
        $status = self::getByName($statusName);
        return $status ? $status->badge_color : 'secondary';
    }

    public static function getDisplayName($statusName)
    {
        $status = self::getByName($statusName);
        return $status ? $status->display_name : ucfirst(str_replace('_', ' ', $statusName));
    }

    // Category-based status lists
    public static function getDraftStatuses()
    {
        return self::getByCategory('draft');
    }

    public static function getApprovalStatuses()
    {
        return self::getByCategory('approval');
    }

    public static function getMisProcessingStatuses()
    {
        return self::getByCategory('mis_processing');
    }

    public static function getCompletionStatuses()
    {
        return self::getByCategory('completion');
    }

    public static function getRejectionStatuses()
    {
        return self::getByCategory('rejection');
    }
}