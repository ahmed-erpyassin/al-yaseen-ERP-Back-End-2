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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            
            // Adjustment Information
            $table->string('adjustment_number')->unique(); // رقم التسوية
            $table->date('adjustment_date'); // تاريخ التسوية
            $table->enum('adjustment_type', ['increase', 'decrease', 'recount']); // نوع التسوية
            $table->enum('reason', ['damaged', 'expired', 'lost', 'found', 'recount', 'other']); // السبب
            
            // Status and Notes
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft'); // الحالة
            $table->text('notes')->nullable(); // ملاحظات
            $table->text('reason_description')->nullable(); // وصف السبب
            
            // Approval Information
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // معتمد من
            $table->timestamp('approved_at')->nullable(); // تاريخ الاعتماد

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'adjustment_date']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
