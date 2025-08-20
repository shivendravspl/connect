<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCategory extends Model
{
    protected $fillable = ['item_id', 'name', 'description', 'status'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}