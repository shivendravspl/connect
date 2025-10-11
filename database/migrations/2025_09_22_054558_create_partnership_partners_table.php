<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('partnership_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                  ->constrained('onboardings')
                  ->onDelete('cascade');
            $table->string('name', 255);
            $table->string('pan', 20)->nullable();
            $table->string('contact', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partnership_partners');
    }
};