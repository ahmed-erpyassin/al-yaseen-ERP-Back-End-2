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
        Schema::table('project_tasks', function (Blueprint $table) {
            // Add records field for links/URLs
            $table->json('records')->nullable()->after('progress')->comment('JSON array of links/URLs related to the task');
            
            // Add notes field (separate from description for additional notes)
            $table->text('notes')->nullable()->after('records')->comment('Additional notes for the task');
            
            // Add task_name field (alias for title, but keeping both for flexibility)
            $table->string('task_name')->nullable()->after('title')->comment('Task name (alias for title)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['records', 'notes', 'task_name']);
        });
    }
};
