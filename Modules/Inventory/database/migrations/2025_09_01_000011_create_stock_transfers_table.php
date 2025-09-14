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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            
            // Transfer Information
            $table->string('transfer_number')->unique(); // رقم النقل
            $table->date('transfer_date'); // تاريخ النقل
            $table->date('expected_date')->nullable(); // التاريخ المتوقع
            $table->date('received_date')->nullable(); // تاريخ الاستلام
            
            // Status and Notes
            $table->enum('status', ['draft', 'sent', 'in_transit', 'received', 'cancelled'])->default('draft'); // الحالة
            $table->text('notes')->nullable(); // ملاحظات
            $table->text('transfer_reason')->nullable(); // سبب النقل
            
            // Approval Information
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // معتمد من
            $table->timestamp('approved_at')->nullable(); // تاريخ الاعتماد
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); // مستلم من

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'transfer_date']);
            $table->index(['company_id', 'from_warehouse_id']);
            $table->index(['company_id', 'to_warehouse_id']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
