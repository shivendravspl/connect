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

        if (Schema::hasTable('distributor_applications')) {
            if (!Schema::hasColumn('distributor_applications', 'region')) {
                Schema::table('distributor_applications', function (Blueprint $table) {
                    $table->integer('region')->nullable()->after('crop_vertical');
                });
            }

            if (!Schema::hasColumn('distributor_applications', 'business_unit')) {
                Schema::table('distributor_applications', function (Blueprint $table) {
                    $table->integer('business_unit')->nullable()->after('zone');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('distributor_applications')) {
            if (Schema::hasColumn('distributor_applications', 'region')) {
                Schema::table('distributor_applications', function (Blueprint $table) {
                    $table->dropColumn('region');
                });
            }
            if (Schema::hasColumn('distributor_applications', 'business_unit')) {
                Schema::table('distributor_applications', function (Blueprint $table) {
                    $table->dropColumn('business_unit');
                });
            }
        }
    }
};
