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
        Schema::table('attendance_records', function (Blueprint $table) {
            // Add missing columns
            $table->time('break_start')->nullable()->after('check_out');
            $table->time('break_end')->nullable()->after('break_start');
            $table->decimal('working_hours', 5, 2)->default(0)->after('break_end');
            $table->decimal('overtime_hours', 5, 2)->default(0)->after('working_hours');
            $table->text('notes')->nullable()->after('overtime_hours');

            // Rename columns to match seeder
            $table->renameColumn('worked_hours', 'old_worked_hours');
            $table->renameColumn('overtimes_hours', 'old_overtime_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['break_start', 'break_end', 'working_hours', 'overtime_hours', 'notes']);
            $table->renameColumn('old_worked_hours', 'worked_hours');
            $table->renameColumn('old_overtime_hours', 'overtimes_hours');
        });
    }
};
