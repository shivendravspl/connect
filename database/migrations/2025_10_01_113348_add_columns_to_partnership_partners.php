<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partnership_partners', function (Blueprint $table) {
            $table->string('aadhar_path')->nullable()->after('contact');
            $table->string('aadhar_original_filename')->nullable()->after('aadhar_path');
        });
    }

    public function down(): void
    {
        Schema::table('partnership_partners', function (Blueprint $table) {
            $table->dropColumn(['aadhar_path', 'aadhar_original_filename']);
        });
    }
};
