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
        Schema::table('onboardings', function (Blueprint $table) {
              // Add new step-wise status columns after `status`
            $table->string('doc_verification_status', 50)->nullable()->after('status');
            $table->string('agreement_status', 50)->nullable()->after('doc_verification_status');
            $table->string('physical_docs_status', 50)->nullable()->after('agreement_status');
            $table->string('final_status', 50)->nullable()->after('physical_docs_status');
            // Drop old column
            $table->dropColumn('mis_verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboardings', function (Blueprint $table) {
             $table->dropColumn([
                'doc_verification_status',
                'agreement_status',
                'physical_docs_status',
                'final_status'
            ]);
            // Re-add old column
            $table->string('mis_verification_status', 50)->nullable()->after('status');
        });
    }
};
