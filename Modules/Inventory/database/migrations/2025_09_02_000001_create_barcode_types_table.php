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
        Schema::create('barcode_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Barcode type code (C128, EAN13, etc.)
            $table->string('name'); // Display name
            $table->string('name_ar'); // Arabic name
            $table->text('description')->nullable(); // Description
            $table->text('description_ar')->nullable(); // Arabic description
            $table->boolean('is_default')->default(false); // Default barcode type
            $table->boolean('is_active')->default(true); // Active status
            $table->json('validation_rules')->nullable(); // Validation rules for this barcode type
            $table->integer('min_length')->nullable(); // Minimum length
            $table->integer('max_length')->nullable(); // Maximum length
            $table->string('pattern')->nullable(); // Regex pattern for validation
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_types');
    }
};
