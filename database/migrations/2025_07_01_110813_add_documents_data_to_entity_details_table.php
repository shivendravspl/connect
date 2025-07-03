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
        Schema::table('entity_details', function (Blueprint $table) {
            // Add documents_data JSON column
            if (!Schema::hasColumn('entity_details', 'documents_data')) {
                $table->json('documents_data')->nullable()->after('additional_data');
            }
            // Add gst_applicable column
            if (!Schema::hasColumn('entity_details', 'gst_applicable')) {
                $table->string('gst_applicable', 10)->nullable()->after('pan_number');
            }
               
             if (!Schema::hasColumn('entity_details', 'gst_number')) {
                $table->string('gst_number')->nullable()->after('gst_applicable');
            }

            // Add seed_license column
            if (!Schema::hasColumn('entity_details', 'seed_license')) {
                $table->string('seed_license')->nullable()->after('gst_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('entity_details')) {
            if (Schema::hasColumn('entity_details', 'documents_data')) {
                Schema::table('entity_details', function (Blueprint $table) {
                    $table->dropColumn('documents_data');
                });
            }
             if (Schema::hasColumn('entity_details', 'gst_applicable')) {
                Schema::table('entity_details', function (Blueprint $table) {
                    $table->dropColumn('gst_applicable');
                });
            }
             if (Schema::hasColumn('entity_details', 'seed_license')) {
                Schema::table('entity_details', function (Blueprint $table) {
                    $table->dropColumn('seed_license');
                });
            }
        }
    }
};
