<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('llp_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                  ->constrained('onboardings')
                  ->onDelete('cascade');
            $table->string('name', 255);
            $table->string('dpin_number', 255);
            $table->string('contact', 20)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('llp_partners');
    }
};