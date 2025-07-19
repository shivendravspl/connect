<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('onboardings', function (Blueprint $table) {
            $table->id();
            $table->string('application_code')->unique();
            $table->unsignedBigInteger('territory')->nullable();
            $table->unsignedBigInteger('crop_vertical');
            $table->unsignedBigInteger('region')->nullable();
            $table->unsignedBigInteger('zone')->nullable();
            $table->unsignedBigInteger('business_unit')->nullable();
            $table->unsignedBigInteger('district');
            $table->unsignedBigInteger('state');           
            $table->string('status')->default('draft');
            $table->unsignedTinyInteger('current_progress_step')->nullable()->default(1);
            $table->unsignedBigInteger('current_approver_id')->nullable();
            $table->unsignedBigInteger('final_approver_id')->nullable();
            $table->string('approval_level')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('onboardings');
    }
};