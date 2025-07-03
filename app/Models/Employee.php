<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'core_employee'; // Specify the table name if different

    protected $fillable = [
       'emp_code', 'title', 'emp_name', 'emp_email', 'emp_contact',
        'emp_desig_id', 'emp_designation', 'emp_desig_code',
        'emp_dept_id', 'emp_department', 'emp_department_code',
        'emp_company_id', 'emp_company', 'emp_company_code',
        'emp_reporting', 'status', 'emp_vertical', 'emp_vertical_name',
        'focus_department', 'territory', 'region', 'zone', 'bu'
    ];

    /**
     * Define the relationship with Territory
     */
    public function territory()
    {
        return $this->belongsTo(CoreTerritory::class, 'territory');
    }

    /**
     * Define the relationship with Region
     */
    public function region()
    {
        return $this->belongsTo(CoreRegion::class, 'region');
    }

    /**
     * Define the relationship with Zone
     */
    public function zone()
    {
        return $this->belongsTo(CoreZone::class, 'zone');
    }

    /**
     * Get the user account for this employee.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'emp_id');
    }

    /**
     * Get the manager to whom this employee reports.
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'emp_reporting');
    }

    /**
     * Get the employees who report to this employee.
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'emp_reporting');
    }
}