<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->foreignId('user_id');
            $table->string('role'); // rbm, zbm, gm, etc.
            $table->string('action'); // approve, reject, revert, hold
            $table->text('remarks')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_logs');
    }
};