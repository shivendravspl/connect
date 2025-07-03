<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('draft_path');
            $table->string('final_path')->nullable();
            $table->string('status')->default('draft'); // draft, finalized
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agreements');
    }
};