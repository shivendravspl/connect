<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model; 


class ApplicationCheckpoint extends Model 
{
    protected $fillable = ['application_id', 'checkpoint_name', 'status', 'reason', 'submitted_by'];

    public function submittedBy()
    {
        return $this->belongsTo(Employee::class, 'submitted_by', 'employee_id');
    }

     
}
