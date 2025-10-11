<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id');//->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('indent_item_id');//->constrained('indent_items')->onDelete('cascade');
            $table->foreignId('item_id');//->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->date('required_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // disable FK temporarily
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('po_items');
        Schema::enableForeignKeyConstraints();
    }
};
