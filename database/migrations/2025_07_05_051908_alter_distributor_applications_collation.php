<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('distributor_applications')) {
            Schema::table('distributor_applications', function (Blueprint $table) {
                if (Schema::hasColumn('distributor_applications', 'territory')) {
                    $table->unsignedBigInteger('territory')->nullable()->change();
                }

                if (Schema::hasColumn('distributor_applications', 'region')) {
                    $table->unsignedBigInteger('region')->nullable()->change();
                }

                if (Schema::hasColumn('distributor_applications', 'zone')) {
                    $table->unsignedBigInteger('zone')->nullable()->change();
                }

                if (Schema::hasColumn('distributor_applications', 'business_unit')) {
                    $table->unsignedBigInteger('business_unit')->nullable()->change();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('distributor_applications')) {
            Schema::table('distributor_applications', function (Blueprint $table) {
                if (Schema::hasColumn('distributor_applications', 'territory')) {
                    $table->string('territory')->nullable()->change();
                }

                if (Schema::hasColumn('distributor_applications', 'region')) {
                    $table->string('region')->nullable()->change();
                }

                if (Schema::hasColumn('distributor_applications', 'zone')) {
                    $table->string('zone')->nullable()->change();
                }

                if (Schema::hasColumn('distributor_applications', 'business_unit')) {
                    $table->string('business_unit')->nullable()->change();
                }
            });
        }
    }
};

