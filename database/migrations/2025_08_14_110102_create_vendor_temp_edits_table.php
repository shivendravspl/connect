<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_temp_edits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('submitted_by');
            $table->string('approval_status')->default('pending');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->string('company_name')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->text('purpose_of_transaction')->nullable();
            $table->string('company_address')->nullable();
            $table->unsignedBigInteger('company_state_id')->nullable();
            $table->string('company_city')->nullable();
            $table->string('pincode', 6)->nullable();
            $table->string('gst_number', 15)->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_number', 10)->nullable();
            $table->unsignedBigInteger('vnr_contact_department_id')->nullable();
            $table->unsignedBigInteger('vnr_contact_person_id')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('legal_status')->nullable();
            $table->string('pan_number', 10)->nullable();
            $table->string('pan_card_copy_path')->nullable();
            $table->string('aadhar_number', 12)->nullable();
            $table->string('aadhar_card_copy_path')->nullable();
            $table->string('gst_certificate_copy_path')->nullable();
            $table->string('msme_number')->nullable();
            $table->string('msme_certificate_copy_path')->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code', 11)->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('cancelled_cheque_copy_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_temp_edits');
    }
};
