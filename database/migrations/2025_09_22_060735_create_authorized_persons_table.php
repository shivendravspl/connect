<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('authorized_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('name', 255);
            $table->string('contact', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('relation', 255)->nullable();
            $table->string('aadhar_number', 12)->nullable();
            $table->string('letter_path')->nullable();
            $table->string('aadhar_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('authorized_persons');
    }
};