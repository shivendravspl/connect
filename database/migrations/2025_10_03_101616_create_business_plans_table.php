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
            $table->string('current_financial_year')->nullable(); // e.g., '2024-25'
            $table->decimal('current_financial_year_mt', 10, 2)->nullable();
            $table->decimal('current_financial_year_amount', 15, 2)->nullable();
            $table->string('next_financial_year')->nullable(); // e.g., '2025-26'
            $table->decimal('next_financial_year_mt', 10, 2)->nullable();
            $table->decimal('next_financial_year_amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_plans');
    }
};