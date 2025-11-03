<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('required_documents_checklist', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('document_name');
            $table->text('checkpoints')->nullable();
            $table->text('applicability_justification')->nullable();
            $table->enum('applicability', ['Mandatory', 'Optional', 'On Applicability'])->default('Mandatory');
            $table->json('entity_types');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['category', 'sub_category']);
            $table->index('applicability');
        });
    }

    public function down()
    {
        Schema::dropIfExists('required_documents_checklist');
    }
};
