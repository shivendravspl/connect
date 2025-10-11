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
            $table->boolean('bank_document_verified')->default(false)->after('bank_document_path');
        });
    }

    public function down()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->dropColumn('bank_document_verified');
        });
    }
};
