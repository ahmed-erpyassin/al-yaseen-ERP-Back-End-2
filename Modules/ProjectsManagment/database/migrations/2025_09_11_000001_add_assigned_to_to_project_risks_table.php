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
        Schema::table('project_risks', function (Blueprint $table) {
            // Add assigned_to field to link with employees table
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('employees')->nullOnDelete();
            
            // Add index for better performance
            $table->index(['assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risks', function (Blueprint $table) {
            // Drop foreign key constraint and index first
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);
            
            // Drop the column
            $table->dropColumn('assigned_to');
        });
    }
};
