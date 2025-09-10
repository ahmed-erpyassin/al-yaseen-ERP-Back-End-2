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
        Schema::table('project_documents', function (Blueprint $table) {
            // Add document_number field for sequential numbering per project
            $table->integer('document_number')->nullable()->after('project_id')->comment('Sequential document number per project');
            
            // Add project_number field (for display purposes)
            $table->string('project_number')->nullable()->after('document_number')->comment('Project number for display');
            
            // Add project_name field (for display purposes)
            $table->string('project_name')->nullable()->after('project_number')->comment('Project name for display');
            
            // Add additional file information fields
            $table->string('file_name')->nullable()->after('file_path')->comment('Original file name');
            $table->string('file_type')->nullable()->after('file_name')->comment('File MIME type');
            $table->bigInteger('file_size')->nullable()->after('file_type')->comment('File size in bytes');
            
            // Add description field
            $table->text('description')->nullable()->after('file_size')->comment('Document description');
            
            // Add document category field
            $table->string('document_category')->nullable()->after('description')->comment('Document category');
            
            // Add status field
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active')->after('document_category')->comment('Document status');
            
            // Add upload_date field
            $table->date('upload_date')->nullable()->after('status')->comment('Document upload date');
            
            // Add version field for document versioning
            $table->string('version')->default('1.0')->after('upload_date')->comment('Document version');
            
            // Add indexes for better performance
            $table->index(['project_id', 'document_number']);
            $table->index(['document_category']);
            $table->index(['status']);
            $table->index(['upload_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['project_id', 'document_number']);
            $table->dropIndex(['document_category']);
            $table->dropIndex(['status']);
            $table->dropIndex(['upload_date']);
            
            // Drop columns
            $table->dropColumn([
                'document_number',
                'project_number',
                'project_name',
                'file_name',
                'file_type',
                'file_size',
                'description',
                'document_category',
                'status',
                'upload_date',
                'version'
            ]);
        });
    }
};
