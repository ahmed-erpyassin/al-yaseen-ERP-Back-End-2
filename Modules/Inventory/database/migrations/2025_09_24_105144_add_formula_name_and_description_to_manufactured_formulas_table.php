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
        Schema::table('manufactured_formulas', function (Blueprint $table) {
            // Add missing formula_name and formula_description columns
            $table->string('formula_name')->nullable()->after('formula_number');
            $table->text('formula_description')->nullable()->after('formula_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufactured_formulas', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn(['formula_name', 'formula_description']);
        });
    }
};
