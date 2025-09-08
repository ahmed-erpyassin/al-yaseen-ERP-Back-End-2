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
        Schema::create('fa_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
             $table->unsignedBigInteger('company_id')->nullable()->unique();
            $table->unsignedBigInteger('branch_id')->nullable()->unique();

            $table->foreignId('journal_entry_id')->nullable()->constrained('journals_entries')->nullOnDelete();
            $table->string('type', 50)->nullable();
            $table->unsignedBigInteger('document_id')->nullable();

            $table->string('file_path', 255);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fa_attachments');
    }
};
