<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('question_key');
            $table->boolean('has_issue');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('declarations');
    }
};