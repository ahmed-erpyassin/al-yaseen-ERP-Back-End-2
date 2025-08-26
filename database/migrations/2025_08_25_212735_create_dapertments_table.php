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
            $table->foreignId('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('manager');
            $table->string('address');
            $table->string('work_phone');
            $table->string('home_phone');
            $table->string('fax');
            $table->text('description');
            $table->text('description_en');
            $table->foreignId('funder_id')->references('id')->on('funders')->cascadeOnDelete();
            $table->tinyInteger('parent_id');
            $table->tinyInteger('status')->default(0);
            $table->date('expected_start_date');
            $table->date('expected_end_date');
            $table->date('actual_start_date');
            $table->date('actual_end_date');
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
