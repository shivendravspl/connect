<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('indent_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('indent_id');
            $table->foreignId('item_id');
            
            // Item details
            $table->decimal('quantity', 10, 2);
            $table->decimal('quantity_approve', 10, 2)->nullable();
            $table->unsignedInteger('sequence')->nullable();
            $table->date('required_date');
            $table->year('financial_year')->nullable();
            $table->string('remarks')->nullable();
            $table->string('status')->default('pending');
            
            // Timestamps
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indent_items');
    }
};