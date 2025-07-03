<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->decimal('net_worth', 15, 2);
            $table->string('shop_ownership'); // owned, rented
            $table->string('godown_area');
            $table->integer('years_in_business');
            $table->json('annual_turnover');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_infos');
    }
};