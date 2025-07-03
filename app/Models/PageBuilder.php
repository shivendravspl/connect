<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageBuilder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'page_name',
        'upper_case',
        'lower_case',
        'snake_case',
        'studly_case'
    ];

    public function scopeStartSorting($query, $request)
    {
        if ($request->has('page_sort_by') && $request->page_sort_by) {
            if ($request->page_direction == "desc") {
                $query->orderByDesc($request->page_sort_by);
            } else {
                $query->orderBy($request->page_sort_by);
            }
        } else {
            $query->orderByDesc("id");
        }
    }

    public function scopeStartSearch($query, $search)
    {
        if ($search) {
            $query->where("id", "like", "%" . $search . "%");
        }
    }
}
