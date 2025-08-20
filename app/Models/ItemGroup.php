<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemGroup extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'created_by'];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}