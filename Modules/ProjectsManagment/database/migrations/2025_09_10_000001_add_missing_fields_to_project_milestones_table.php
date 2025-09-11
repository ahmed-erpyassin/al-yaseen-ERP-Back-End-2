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
        Schema::table('project_milestones', function (Blueprint $table) {
            // Add milestone_number field for sequential numbering per project
            $table->integer('milestone_number')->nullable()->after('project_id')->comment('Sequential milestone number per project');
            
            // Add notes field for additional user notes
            $table->text('notes')->nullable()->after('progress')->comment('Additional notes for the milestone');
            
            // Add index for milestone_number with project_id for uniqueness
            $table->index(['project_id', 'milestone_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'milestone_number']);
            $table->dropColumn(['milestone_number', 'notes']);
        });
    }
};
