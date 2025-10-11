<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trust_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                  ->constrained('onboardings')
                  ->onDelete('cascade');
            $table->string('reg_number', 255);
            $table->date('reg_date');
            $table->timestamps();
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('trust_details');
    }
};