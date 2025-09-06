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

            $table->foreignId('user_id')->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('set null');

            $table->string('code', 5)->unique(); // ISO code مثل "PS", "SA"
            $table->string('name', 150);         // الاسم بالعربية
            $table->string('name_en', 150);      // الاسم بالإنجليزية
            $table->string('phone_code', 10)->nullable();     // كود الاتصال الدولي
            $table->string('currency_code', 10)->nullable();  // كود العملة
            $table->string('timezone', 50)->nullable();       // المنطقة الزمنية

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
        Schema::dropIfExists('countries');
    }
};
