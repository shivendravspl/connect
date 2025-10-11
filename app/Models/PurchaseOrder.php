<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_no', 'indent_id', 'vendor_id', 'po_date', 'expected_delivery_date',
        'total_amount', 'terms', 'status', 'created_by'
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
    ];

    public function indent()
    {
        return $this->belongsTo(Indent::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PoItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}