<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationControl extends Model
{

    protected $table = 'communication_controls';

    protected $fillable = ['key', 'description', 'is_active', 'created_by', 'updated_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
