<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'menu_name',
        'menu_url',
        'parent_id',
        'menu_position',
        'permissions'
    ];

    protected $appends = ['children'];

    public function getChildrenAttribute()
    {
        if (!array_key_exists('children', $this->attributes)) {
            $this->attributes['children'] = [];
        }

        return $this->attributes['children'];
    }

    public function setChildrenAttribute($value)
    {
        $this->attributes['children'] = $value;
    }
}
