<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('onboardings')->onDelete('cascade'); // Link to application
            $table->enum('mode', ['transport', 'courier', 'by_hand']); // Dispatch mode
            $table->string('transport_name')->nullable(); // For transport mode
            $table->string('driver_name')->nullable(); // For transport mode
            $table->string('driver_contact')->nullable(); // For transport mode
            $table->string('docket_number')->nullable(); // For courier mode
            $table->string('courier_company_name')->nullable(); // For courier mode
            $table->string('person_name')->nullable(); // For by_hand mode
            $table->string('person_contact')->nullable(); // For by_hand mode
            $table->date('dispatch_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->date('receive_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_dispatches');
    }
};