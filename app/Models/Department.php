<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    // Table name (optional if it's plural of the model name)
    protected $table = 'core_department';

    // Primary key (optional, default is 'id')
    protected $primaryKey = 'id';

    // Disable timestamps if not present in table
    public $timestamps = false;

    // Fillable columns
    protected $fillable = [
        'department_name',
        'department_code',
        'effective_date',
        'is_active',
        'numeric_code',
    ];
}
