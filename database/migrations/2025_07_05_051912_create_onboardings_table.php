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
            $table->string('territory')->nullable();
            $table->string('crop_vertical');
            $table->string('region')->nullable();
            $table->string('zone')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('district');
            $table->string('state');           
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