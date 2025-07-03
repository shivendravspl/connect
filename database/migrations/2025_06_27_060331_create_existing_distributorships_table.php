<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('existing_distributorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('company_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('existing_distributorships');
    }
};