<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_document_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('document_type');
            $table->boolean('received')->default(false);
            $table->enum('status', ['verified', 'not_verified', 'pending'])->default('pending');
            $table->text('reason')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->date('verified_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_document_checks');
    }
};
