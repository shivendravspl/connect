<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_group_id','name', 'code', 'description',  'uom',
        'is_active'
    ];

    public function itemGroup(): BelongsTo
    {
        return $this->belongsTo(ItemGroup::class);
    }

     public function categories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }
    
    public function indentItems()
    {
        return $this->hasMany(IndentItem::class);
    }
}
