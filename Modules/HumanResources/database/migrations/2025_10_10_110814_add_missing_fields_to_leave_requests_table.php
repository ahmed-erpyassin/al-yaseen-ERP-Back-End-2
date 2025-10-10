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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Add missing columns
            $table->string('leave_type')->after('employee_id'); // Add string leave_type
            $table->integer('days_requested')->after('end_date');
            $table->text('reason')->nullable()->after('days_requested');

            // Make approved_at nullable
            $table->timestamp('approved_at')->nullable()->change();

            // Add soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['leave_type', 'days_requested', 'reason']);
            $table->dropSoftDeletes();
        });
    }
};
