<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'indent_id',
        'item_id',
        'quantity',
        'quantity_approve',
        'sequence',
        'required_date',
        'financial_year',
        'remarks',
        'status'
    ];

    protected $casts = [
        'required_date' => 'date',
        'quantity' => 'decimal:2'
    ];

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}