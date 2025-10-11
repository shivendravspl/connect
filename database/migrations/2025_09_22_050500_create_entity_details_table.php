<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('entity_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('establishment_name', 255);
            $table->string('entity_type', 255);
            $table->text('business_address')->nullable();
            $table->string('house_no', 255)->nullable();
            $table->string('landmark', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('country_id')->default(1);
            $table->string('pincode', 255);
            $table->string('mobile', 255);
            $table->string('email', 255)->nullable();

            // PAN Details
            $table->string('pan_number', 255);
            $table->string('pan_path')->nullable();
            $table->boolean('pan_verified')->default(false);

            // GST Details
            $table->enum('gst_applicable', ['yes', 'no'])->nullable();
            $table->string('gst_number', 255)->nullable();
            $table->string('gst_path')->nullable();
            $table->date('gst_validity')->nullable();
            $table->boolean('gst_verified')->default(false);

            // Seed License
            $table->string('seed_license', 255)->nullable();
            $table->string('seed_license_path')->nullable();
            $table->date('seed_license_validity')->nullable();
            $table->boolean('seed_license_verified')->default(false);

            // Bank Details
            $table->string('bank_name', 255)->nullable();
            $table->string('account_holder_name', 255)->nullable();
            $table->string('account_number', 255)->nullable();
            $table->string('ifsc_code', 11)->nullable();
            $table->string('bank_document_path')->nullable();

            // Additional Identifiers
            $table->string('tan_number', 10)->nullable();

            $table->timestamps();

            // Indexes
            $table->index('application_id');
            $table->index('entity_type');
            $table->index('pan_number');
            $table->index('gst_number');
            $table->index('seed_license');
            $table->index('account_number');
            $table->index('ifsc_code');
            $table->index(['application_id', 'entity_type']);
            $table->index(['pan_number', 'pan_verified']);
            $table->index(['gst_applicable', 'gst_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('entity_details');
    }
};
