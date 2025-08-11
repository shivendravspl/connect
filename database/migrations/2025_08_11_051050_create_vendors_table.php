<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('nature_of_business')->nullable(); // e.g. goods, service, etc.
            $table->text('purpose_of_transaction')->nullable();
            $table->string('company_address')->nullable();
            $table->unsignedBigInteger('company_state_id')->nullable();
            $table->string('company_city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('vnrs_contact_person_name')->nullable();
            $table->string('payment_terms')->nullable();

            // Legal Information
            $table->string('legal_status')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('pan_card_copy_path')->nullable();
            $table->string('aadhar_number')->nullable();
            $table->string('aadhar_card_copy_path')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('gst_certificate_copy_path')->nullable();
            $table->string('msme_number')->nullable();
            $table->string('msme_certificate_copy')->nullable();

            // Banking Information
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('cancelled_cheque_copy_path')->nullable();

            // Progress tracking
            $table->unsignedTinyInteger('current_step')->default(1); // Track current step
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
