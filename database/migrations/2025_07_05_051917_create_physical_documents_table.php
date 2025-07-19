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
        if (!Schema::hasTable('physical_documents')) {
            Schema::create('physical_documents', function (Blueprint $table) {
                $table->id();
                 $table->foreignId('application_id');
                $table->boolean('agreement_received')->default(false);
                $table->date('agreement_received_date')->nullable();
                $table->boolean('agreement_verified')->default(false);
                $table->date('agreement_verified_date')->nullable();
                $table->foreignId('agreement_verified_by')->nullable();
                $table->boolean('security_cheque_received')->default(false);
                $table->date('security_cheque_received_date')->nullable();
                $table->boolean('security_cheque_verified')->default(false);
                $table->date('security_cheque_verified_date')->nullable();
                $table->foreignId('security_cheque_verified_by')->nullable();
                $table->boolean('security_deposit_received')->default(false);
                $table->date('security_deposit_received_date')->nullable();
                $table->boolean('security_deposit_verified')->default(false);
                $table->date('security_deposit_verified_date')->nullable();
                $table->foreignId('security_deposit_verified_by')->nullable();
                $table->decimal('security_deposit_amount', 10, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_documents');
    }
};
