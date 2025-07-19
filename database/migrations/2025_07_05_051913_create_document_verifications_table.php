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
        if (!Schema::hasTable('document_verifications')) {
            Schema::create('document_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id');
                $table->string('document_type');
                $table->string('status'); // verified, rejected
                $table->text('remarks')->nullable();
                $table->foreignId('verified_by');
                $table->timestamp('verified_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
