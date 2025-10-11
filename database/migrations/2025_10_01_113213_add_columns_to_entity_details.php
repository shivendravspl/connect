<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->string('entity_proof_path')->nullable()->after('seed_license_verified');
            $table->string('ownership_info_path')->nullable()->after('entity_proof_path');
            $table->string('bank_statement_path')->nullable()->after('ownership_info_path');
            $table->string('itr_acknowledgement_path')->nullable()->after('bank_statement_path');
            $table->string('balance_sheet_path')->nullable()->after('itr_acknowledgement_path');
        });
    }

    public function down()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->dropColumn([
                'entity_proof_path',
                'ownership_info_path',
                'bank_statement_path',
                'itr_acknowledgement_path',
                'balance_sheet_path'
            ]);
        });
    }
};
