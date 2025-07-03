<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormBuilder extends Model
{
    public $table = 'form_builder';
    public $timestamps = false;
    protected $fillable = [
        'page_id', 'page_name', 'input_type', 'column_title', 'column_name', 'column_width', 'is_required', 'is_unique', 'is_nullable',
        'is_switch', 'default_value', 'placeholder', 'source_table', 'source_table_column_key',
        'source_table_column_value', 'sorting_order', 'column_type','description','column_length','min_value','max_value'
    ];
}
