<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('distributor_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_code')->unique();
            $table->string('territory');
            $table->string('crop_vertical');
            $table->string('zone');
            $table->string('district');
            $table->string('state');
            $table->string('status')->default('draft');
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributor_applications');
    }
};