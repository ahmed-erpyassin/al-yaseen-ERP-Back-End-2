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
        Schema::table('currencies', function (Blueprint $table) {
            // Add missing fields that are referenced in the code
            $table->boolean('is_active')->default(true)->after('decimal_places');
            $table->boolean('is_base_currency')->default(false)->after('is_active');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('is_base_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_base_currency', 'exchange_rate']);
        });
    }
};
