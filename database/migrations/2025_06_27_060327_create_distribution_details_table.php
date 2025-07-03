<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('distribution_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->json('area_covered'); 
            $table->string('appointment_type', 20);
            $table->text('replacement_reason')->nullable();
            $table->text('outstanding_recovery')->nullable();
            $table->string('previous_firm_name', 255)->nullable();
            $table->string('previous_firm_code', 100)->nullable();
            $table->string('earlier_distributor', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('distribution_details');
    }
};