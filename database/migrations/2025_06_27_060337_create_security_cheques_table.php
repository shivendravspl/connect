<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('security_cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('cheque_number');
            $table->string('bank_name');
            $table->string('status')->default('pending'); // pending, verified
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('security_cheques');
    }
};