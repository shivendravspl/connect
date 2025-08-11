<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Company Information
        'company_name',
        'nature_of_business',
        'purpose_of_transaction',
        'company_address',
        'company_state_id',
        'company_city',
        'pincode',
        'vendor_email',
        'contact_person_name',
        'contact_number',
        'vnrs_contact_person_name',
        'payment_terms',
        
        // Legal Information
        'legal_status',
        'pan_number',
        'pan_card_copy_path',
        'aadhar_number',
        'aadhar_card_copy_path',
        'gst_number',
        'gst_certificate_copy_path',
        'msme_number',
        'msme_certificate_copy_path',
        
        // Banking Information
        'bank_account_holder_name',
        'bank_account_number',
        'ifsc_code',
        'bank_branch',
        'cancelled_cheque_copy_path',
        
        // Progress tracking
        'current_step',
        'is_completed'
    ];

    public function state()
    {
        return $this->belongsTo(CoreState::class, 'company_state_id');
    }
}