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
        // Only create if it doesn't exist
        if (!Schema::hasTable('distributor_masters')) {
            Schema::create('distributor_masters', function (Blueprint $table) {
                $table->id();

                // Foreign keys
                $table->foreignId('application_id');
                $table->foreignId('territory_id');
                // Distributor fields
                $table->string('distributor_code')->unique();
                $table->string('name');
                $table->string('entity_type');
                $table->string('pan_number');
                $table->string('gst_number');
                $table->date('agreement_date');
                $table->decimal('security_cheque_amount', 10, 2);
                $table->decimal('security_deposit_amount', 10, 2);
                $table->string('status'); // active, inactive, terminated
                $table->foreignId('created_by');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_masters');
    }
};
