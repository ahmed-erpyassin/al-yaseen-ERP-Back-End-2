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
        Schema::table('employee_promotions', function (Blueprint $table) {
            // Add missing columns
            $table->decimal('old_salary', 10, 2)->after('new_job_title_id');
            $table->decimal('new_salary', 10, 2)->after('old_salary');
            $table->string('promotion_reason')->nullable()->after('promotion_date');
            $table->date('effective_date')->after('promotion_reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('effective_date');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');

            // Add soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_promotions', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['old_salary', 'new_salary', 'promotion_reason', 'effective_date', 'status', 'approved_by']);
            $table->dropSoftDeletes();
        });
    }
};
