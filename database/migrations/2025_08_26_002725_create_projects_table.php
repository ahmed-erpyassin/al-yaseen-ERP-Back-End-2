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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('notebook');
            $table->string('project_number')->unique();
            $table->date('date');
            $table->time('time');
            $table->string('phone');
            $table->string('name');
            $table->string('email');
            $table->string('licensed_operator');
            $table->foreignId('currency_id')->references('id')->on('currencies')->cascadeOnDelete();
            $table->decimal('currency_price', 15, 2);
            $table->boolean('include_vat')->default(false);
            $table->string('project_name');
            $table->string('manager_name');
            $table->string('opportunity');
            $table->text('statement');
            $table->foreignId('country_id')->references('id')->on('countries')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['0', '1', '2', '3'])->default('0');
            $table->text('notes')->nullable();
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
