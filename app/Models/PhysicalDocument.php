<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalDocument extends Model
{
    protected $table = 'physical_documents';
    protected $fillable = [
        'application_id', 'agreement_received', 'agreement_received_date',
        'agreement_verified', 'agreement_verified_date', 'agreement_verified_by',
        'security_cheque_received', 'security_cheque_received_date',
        'security_cheque_verified', 'security_cheque_verified_date', 'security_cheque_verified_by',
        'security_deposit_received', 'security_deposit_received_date',
        'security_deposit_verified', 'security_deposit_verified_date', 'security_deposit_verified_by',
        'security_deposit_amount'
    ];

    protected $casts = [
        'agreement_received' => 'boolean',
        'agreement_verified' => 'boolean',
        'security_cheque_received' => 'boolean',
        'security_cheque_verified' => 'boolean',
        'security_deposit_received' => 'boolean',
        'security_deposit_verified' => 'boolean',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }

    public function agreementVerifiedBy()
    {
        return $this->belongsTo(Employee::class, 'agreement_verified_by', 'employee_id');
    }

    public function securityChequeVerifiedBy()
    {
        return $this->belongsTo(Employee::class, 'security_cheque_verified_by', 'employee_id');
    }

    public function securityDepositVerifiedBy()
    {
        return $this->belongsTo(Employee::class, 'security_deposit_verified_by', 'employee_id');
    }
}