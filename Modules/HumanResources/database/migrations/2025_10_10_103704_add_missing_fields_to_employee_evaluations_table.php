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
        Schema::table('employee_evaluations', function (Blueprint $table) {
            // Add missing columns
            $table->foreignId('evaluator_id')->nullable()->after('employee_id')->constrained('users')->onDelete('set null');
            $table->string('evaluation_period')->after('evaluator_id');
            $table->integer('performance_score')->nullable()->after('score');
            $table->integer('goals_achievement')->nullable()->after('performance_score');
            $table->integer('communication_skills')->nullable()->after('goals_achievement');
            $table->integer('teamwork')->nullable()->after('communication_skills');
            $table->integer('leadership')->nullable()->after('teamwork');
            $table->integer('overall_rating')->nullable()->after('leadership');
            $table->text('strengths')->nullable()->after('overall_rating');
            $table->text('areas_for_improvement')->nullable()->after('strengths');
            $table->text('goals_next_period')->nullable()->after('areas_for_improvement');
            $table->text('evaluator_comments')->nullable()->after('goals_next_period');
            $table->text('employee_comments')->nullable()->after('evaluator_comments');
            $table->enum('status', ['draft', 'completed', 'approved'])->default('draft')->after('employee_comments');

            // Rename existing columns
            $table->renameColumn('comments', 'notes');

            // Add soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_evaluations', function (Blueprint $table) {
            $table->dropForeign(['evaluator_id']);
            $table->dropColumn([
                'evaluator_id', 'evaluation_period', 'performance_score', 'goals_achievement',
                'communication_skills', 'teamwork', 'leadership', 'overall_rating',
                'strengths', 'areas_for_improvement', 'goals_next_period',
                'evaluator_comments', 'employee_comments', 'status'
            ]);
            $table->renameColumn('notes', 'comments');
            $table->dropSoftDeletes();
        });
    }
};
