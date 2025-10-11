<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('company_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')
                  ->constrained('onboardings')
                  ->onDelete('cascade');
            $table->enum('entity_type', ['private_company', 'public_company']);
            $table->string('cin_number', 255);
            $table->date('incorporation_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_details');
    }
};