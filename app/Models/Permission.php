<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public $timestamps = false;
    protected $fillable = ['name', 'group_name', 'module', 'guard_name'];
}
