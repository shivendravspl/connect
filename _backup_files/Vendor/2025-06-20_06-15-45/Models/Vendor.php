<?php

namespace App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FileUploadTrait;
use Illuminate\Support\Facades\Auth;

class Vendor extends Model
{
    use SoftDeletes;
    use FileUploadTrait;

    
    protected $table = 'vendor';

    
    protected $fillable = ['email', 'company_name', 'state'];

    protected $dates = ['created_at','updated_at','deleted_at'];
    
    
    public function scopeStartSorting($query, $request): void
    {
        if ($request->has('vendor_sort_by') && $request->vendor_sort_by) {
        if ($request->vendor_direction == "desc"){
            $query->orderByDesc($request->vendor_sort_by);
            } else {
            $query->orderBy($request->vendor_sort_by);
            }
        } else {
             $query->orderByDesc("id");
         }
    }
    public function scopeStartSearch($query, $search): void
    {
        if ($search) {
            $query->where("id","like","%".$search."%");
        }
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
        static::deleting(function ($model) {
            if (Auth::check() && ! $model->isForceDeleting()) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }
}
