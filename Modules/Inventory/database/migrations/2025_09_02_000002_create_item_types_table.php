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
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code')->unique(); // Type code (service, goods, etc.)
            $table->string('name'); // Display name
            $table->string('name_ar'); // Arabic name
            $table->text('description')->nullable(); // Description
            $table->text('description_ar')->nullable(); // Arabic description
            $table->boolean('is_system')->default(false); // System-defined type (cannot be deleted)
            $table->boolean('is_active')->default(true); // Active status
            $table->integer('sort_order')->default(0); // Sort order in dropdown
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['is_system', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
