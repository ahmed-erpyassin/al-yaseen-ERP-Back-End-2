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
        Schema::table('departments', function (Blueprint $table) {
            // Make optional fields nullable
            $table->text('address')->nullable()->change();
            $table->string('work_phone', 25)->nullable()->change();
            $table->string('home_phone', 25)->nullable()->change();
            $table->string('fax', 50)->nullable()->change();
            $table->string('statement', 150)->nullable()->change();
            $table->string('statement_en', 150)->nullable()->change();
            $table->unsignedBigInteger('parent_id')->nullable()->change();
            $table->unsignedBigInteger('funder_id')->nullable()->change();
            $table->date('proposed_start_date')->nullable()->change();
            $table->date('proposed_end_date')->nullable()->change();
            $table->date('actual_start_date')->nullable()->change();
            $table->date('actual_end_date')->nullable()->change();
            $table->unsignedBigInteger('budget_id')->nullable()->change();
            $table->foreignId('created_by')->nullable()->change();
            $table->foreignId('updated_by')->nullable()->change();
            $table->foreignId('deleted_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Revert nullable changes (make required again)
            $table->text('address')->nullable(false)->change();
            $table->string('work_phone', 25)->nullable(false)->change();
            $table->string('home_phone', 25)->nullable(false)->change();
            $table->string('fax', 50)->nullable(false)->change();
            $table->string('statement', 150)->nullable(false)->change();
            $table->string('statement_en', 150)->nullable(false)->change();
            $table->unsignedBigInteger('parent_id')->nullable(false)->change();
            $table->unsignedBigInteger('funder_id')->nullable(false)->change();
            $table->date('proposed_start_date')->nullable(false)->change();
            $table->date('proposed_end_date')->nullable(false)->change();
            $table->date('actual_start_date')->nullable(false)->change();
            $table->date('actual_end_date')->nullable(false)->change();
            $table->unsignedBigInteger('budget_id')->nullable(false)->change();
            $table->foreignId('created_by')->nullable(false)->change();
            $table->foreignId('updated_by')->nullable(false)->change();
            $table->foreignId('deleted_by')->nullable(false)->change();
        });
    }
};
