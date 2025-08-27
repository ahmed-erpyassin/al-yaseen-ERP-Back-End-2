<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incoming_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incoming_order_id')->constrained('incoming_orders')->cascadeOnDelete();

            $table->string('item_number');
            $table->string('item_name');
            $table->string('unit', 50);
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_order_items');
    }
};
