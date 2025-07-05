<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('distributor_applications')) {
            Schema::table('distributor_applications', function (Blueprint $table) {
                if (!Schema::hasColumn('distributor_applications', 'current_approver_id')) {
                    $table->unsignedBigInteger('current_approver_id')->nullable()->after('status');
                }

                if (!Schema::hasColumn('distributor_applications', 'approval_level')) {
                    $table->string('approval_level')->nullable()->after('current_approver_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('distributor_applications')) {
            Schema::table('distributor_applications', function (Blueprint $table) {
                if (Schema::hasColumn('distributor_applications', 'current_approver_id')) {
                    $table->dropColumn('current_approver_id');
                }

                if (Schema::hasColumn('distributor_applications', 'approval_level')) {
                    $table->dropColumn('approval_level');
                }
            });
        }
    }
};
