<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->decimal('net_worth', 15, 2)->nullable();
            $table->enum('shop_ownership', ['owned', 'rented', 'lease'])->nullable();
            $table->enum('shop_uom', ['sq_ft', 'sq_m'])->nullable();
            $table->decimal('shop_area', 10, 2)->nullable();
            $table->enum('godown_uom', ['sq_ft', 'sq_m'])->nullable();
            $table->decimal('godown_area', 10, 2)->nullable();
            $table->enum('godown_ownership', ['owned', 'rented'])->nullable();
            $table->integer('years_in_business')->nullable();
            $table->json('annual_turnover')->nullable();  // Added: For dynamic FY data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_infos');
    }
};
