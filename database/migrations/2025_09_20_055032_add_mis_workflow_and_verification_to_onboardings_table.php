<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboardings', function (Blueprint $table) {
            // Add MIS workflow fields
            $table->boolean('is_hierarchy_approved')->default(false)->after('status');
            $table->timestamp('mis_rejected_at')->nullable()->after('is_hierarchy_approved');
            $table->timestamp('resubmitted_at')->nullable()->after('mis_rejected_at');
            
            // Add MIS verification tracking
            $table->timestamp('mis_verified_at')->nullable()->after('resubmitted_at');
            $table->string('mis_verification_status')->nullable()->after('mis_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('onboardings', function (Blueprint $table) {
            $table->dropColumn([
                'is_hierarchy_approved',
                'mis_rejected_at',
                'resubmitted_at',
                'mis_verified_at',
                'mis_verification_status'
            ]);
        });
    }
};