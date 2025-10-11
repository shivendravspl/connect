<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationAdditionalUploadsTable extends Migration
{
    public function up()
    {
        Schema::create('application_additional_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('additional_doc_id');
            $table->string('path')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_additional_uploads');
    }
}