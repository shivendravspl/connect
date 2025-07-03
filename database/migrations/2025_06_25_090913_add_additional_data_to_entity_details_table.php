<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalDataToEntityDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->json('additional_data')->nullable()->after('seed_license');
        });
    }

    public function down()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->dropColumn('additional_data');
        });
    }
}