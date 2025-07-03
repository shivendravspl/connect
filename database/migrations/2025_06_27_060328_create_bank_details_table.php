<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
             // Financial info
            $table->enum('financial_status', ['Average', 'Good', 'Very good', 'Excellent'])->nullable();
            $table->string('retailer_count')->nullable(); 
            // Bank info
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('account_number');
            $table->string('ifsc_code');
            $table->string('account_type');
            $table->string('relationship_duration')->nullable();
            $table->string('od_limit')->nullable();
            $table->string('od_security')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_details');
    }
};