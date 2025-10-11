<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'indent_no',
        'indent_date',
        'requested_by',
        'department_id',
        'estimated_supply_date',
        'order_by',
        'quotation_file',
        'purpose',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'indent_date' => 'date',
        'estimated_supply_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(IndentItem::class);
    }

    public function getStatusClassAttribute()
    {
        $statusClasses = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'processed' => 'primary'
        ];

        return $statusClasses[$this->status] ?? 'secondary';
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->count();
    }

    public function orderByUser()
    {
        return $this->belongsTo(User::class, 'order_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'indent_id');
    }
}
