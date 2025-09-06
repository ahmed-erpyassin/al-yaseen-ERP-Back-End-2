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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('set null');
            $table->foreignId('manager_id')->constrained('users')->onDelete('set null');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('set null');
            $table->foreignId('country_id')->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->constrained('regions')->onDelete('set null');
            $table->foreignId('city_id')->constrained('cities')->onDelete('set null');

            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('address', 255)->nullable();
            $table->string('landline', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('timezone', 50)->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->constrained('users')->onDelete('set null');

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
