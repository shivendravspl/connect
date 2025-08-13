<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'emp_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('emp_status', ['A', 'D'])
                      ->nullable()
                      ->after('emp_id')
                      ->comment('A=Active, D=Disabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'emp_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('emp_status');
            });
        }
    }
};
