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
            $table->foreignId('application_id');
            $table->string('establishment_name');
            $table->string('entity_type'); // individual, sole_proprietorship, etc.
            $table->text('business_address')->nullable();
            $table->string('house_no')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('country_id')->default(1);
            $table->string('pincode');
            $table->string('mobile');
            $table->string('email');
            $table->string('pan_number');
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entity_details');
    }
};