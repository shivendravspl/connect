<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('indents', function (Blueprint $table) {
            $table->id();
            $table->string('indent_no')->unique();
            $table->date('indent_date');
            $table->date('estimated_supply_date');
            
            // Foreign keys
            $table->foreignId('requested_by');
            $table->foreignId('department_id');
            $table->foreignId('order_by');
            
            // Other fields
            $table->text('purpose');
            $table->string('quotation_file')->nullable();
            $table->string('status')->default('U');
            
            // Approval fields
            $table->foreignId('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Soft delete and timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('indents');
    }
};