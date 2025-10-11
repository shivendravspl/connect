<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'indent_item_id', 'item_id', 'quantity',
        'unit_price', 'subtotal', 'required_date', 'remarks'
    ];

    protected $casts = [
        'required_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function indentItem()
    {
        return $this->belongsTo(IndentItem::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}