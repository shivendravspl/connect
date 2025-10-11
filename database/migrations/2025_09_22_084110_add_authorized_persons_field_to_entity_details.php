<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->enum('has_authorized_persons', ['yes', 'no'])->default('no')->after('tan_number');
        });
    }

    public function down()
    {
        Schema::table('entity_details', function (Blueprint $table) {
            $table->dropColumn('has_authorized_persons');
        });
    }
};