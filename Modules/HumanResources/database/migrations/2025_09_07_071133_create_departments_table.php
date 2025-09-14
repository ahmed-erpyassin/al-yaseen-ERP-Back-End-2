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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('fiscal_year_id');
            $table->string('name')->nullable();
            $table->integer('number');
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->text('address');
            $table->string('work_phone', 25);
            $table->string('home_phone' , 25);
            $table->string('fax' , 50);
            $table->string('statement' , 150);
            $table->string('statement_en' , 150);
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('funder_id');
            $table->enum('project_status', ['not_started', 'inprogress', 'completed', 'paused', 'canceled']);
            $table->enum('status', ['active', 'inactive']);
            $table->date('proposed_start_date');
            $table->date('proposed_end_date');
            $table->date('actual_start_date');
            $table->date('actual_end_date');
            $table->unsignedBigInteger('budget_id');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('deleted_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
