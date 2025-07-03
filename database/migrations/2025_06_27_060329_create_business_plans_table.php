<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('business_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('crop');
            $table->json('yearly_targets');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_plans');
    }
};