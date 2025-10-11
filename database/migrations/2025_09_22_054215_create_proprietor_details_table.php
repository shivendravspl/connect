<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proprietor_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->string('name', 255);
            $table->date('dob');
            $table->string('father_name', 255);
            $table->integer('age');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proprietor_details');
    }
};