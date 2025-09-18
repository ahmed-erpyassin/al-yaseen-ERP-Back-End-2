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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('code', 5)->unique(); // ISO code مثل "PS", "SA"
            $table->string('name', 150)->unique();         // الاسم بالعربية
            $table->string('name_en', 150)->unique();      // الاسم بالإنجليزية
            $table->string('phone_code', 10)->nullable();     // كود الاتصال الدولي
            $table->string('currency_code', 10)->nullable();  // كود العملة
            $table->string('timezone', 50)->nullable();       // المنطقة الزمنية

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
