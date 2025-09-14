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
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('branch_name_ar'); // اسم الفرع
            $table->string('branch_name_en')->nullable(); // Branch Name (English)
            $table->string('branch_code')->nullable(); // كود الفرع
            $table->string('manager_name')->nullable(); // اسم المدير
            $table->string('phone')->nullable(); // الهاتف
            $table->string('email')->nullable(); // البريد الإلكتروني
            $table->text('address')->nullable(); // العنوان
            $table->boolean('active')->default(true); // نشط
            $table->timestamps();

            $table->index(['company_id', 'active']);
            $table->unique(['company_id', 'branch_code']);
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
