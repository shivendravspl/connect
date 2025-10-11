<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no')->unique(); // e.g., PO/0001
            $table->foreignId('indent_id');//->constrained('indents')->onDelete('cascade');
            $table->foreignId('vendor_id');//->constrained('vendors')->onDelete('cascade');
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('terms')->nullable();
            $table->enum('status', ['draft','approved','issued','sent','acknowledged','completed','cancelled'])->default('draft');
            $table->foreignId('created_by');//->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // disable FK temporarily
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_orders');
        Schema::enableForeignKeyConstraints();
    }
};
