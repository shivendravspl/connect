<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTempEdit extends Model
{
    use HasFactory;

    protected $table = 'vendor_temp_edits';

    protected $fillable = [
        'vendor_id',
        'submitted_by',
        'approval_status',
        'is_active',
        'is_completed',
        'current_step',
        'company_name',
        'nature_of_business',
        'purpose_of_transaction',
        'company_address',
        'company_state_id',
        'company_city',
        'pincode',
        'gst_number',
        'vendor_email',
        'contact_person_name',
        'contact_number',
        'vnr_contact_department_id',
        'vnr_contact_person_id',
        'payment_terms',
        'legal_status',
        'pan_number',
        'pan_card_copy_path',
        'aadhar_number',
        'aadhar_card_copy_path',
        'gst_certificate_copy_path',
        'msme_number',
        'msme_certificate_copy_path',
        'bank_account_holder_name',
        'bank_account_number',
        'bank_name',
        'ifsc_code',
        'bank_branch',
        'cancelled_cheque_copy_path',
        'agreement_copy_path',
        'other_documents_path',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function state()
    {
        return $this->belongsTo(CoreState::class, 'company_state_id');
    }

    public function department()
    {
        return $this->belongsTo(CoreDepartment::class, 'vnr_contact_department_id');
    }

    public function vnrContactPerson()
    {
        return $this->belongsTo(Employee::class, 'vnr_contact_person_id');
    }
}
