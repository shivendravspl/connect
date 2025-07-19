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
        if (!Schema::hasTable('distributor_agreements')) {
            Schema::create('distributor_agreements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id');
                $table->string('agreement_path');
                $table->foreignId('generated_by');
                $table->timestamp('generated_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_agreements');
    }
};
