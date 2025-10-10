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
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('document_name')->nullable()->after('document_type');
            $table->string('file_size')->nullable()->after('file_path');
            $table->date('upload_date')->nullable()->after('file_size');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active')->after('expiry_date');
            $table->text('notes')->nullable()->after('status');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn(['document_name', 'file_size', 'upload_date', 'status', 'notes']);
            $table->dropSoftDeletes();
        });
    }
};
