<?php

namespace Modules\ProjectsManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Optional fields for update
            'project_id' => 'sometimes|exists:projects,id',
            'title' => 'sometimes|string|max:255',
            'file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar',
            
            // Optional fields
            'project_number' => 'nullable|string|max:100',
            'project_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'document_category' => 'nullable|in:contract,specification,drawing,report,invoice,correspondence,other',
            'status' => 'nullable|in:active,archived,deleted',
            'upload_date' => 'nullable|date',
            'version' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'Selected project does not exist.',
            'title.max' => 'Document title cannot exceed 255 characters.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'File size cannot exceed 20MB.',
            'file.mimes' => 'File must be one of the following types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG, GIF, ZIP, RAR.',
            'description.max' => 'Description cannot exceed 2000 characters.',
            'document_category.in' => 'Invalid document category selected.',
            'status.in' => 'Invalid status selected. Must be: active, archived, or deleted.',
            'upload_date.date' => 'Upload date must be a valid date.',
            'version.max' => 'Version cannot exceed 20 characters.',
            'project_number.max' => 'Project number cannot exceed 100 characters.',
            'project_name.max' => 'Project name cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'Project',
            'title' => 'Document Title',
            'file' => 'File',
            'description' => 'Description',
            'document_category' => 'Document Category',
            'status' => 'Status',
            'upload_date' => 'Upload Date',
            'version' => 'Version',
            'project_number' => 'Project Number',
            'project_name' => 'Project Name',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-populate system fields from authenticated user
        if ($this->user()) {
            $this->merge([
                'updated_by' => $this->user()->id,
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation: Check if project belongs to user's company
            if ($this->project_id && $this->user()) {
                $project = \Modules\ProjectsManagment\Models\Project::find($this->project_id);
                if ($project && $project->company_id !== $this->user()->company_id) {
                    $validator->errors()->add('project_id', 'Selected project does not belong to your company.');
                }
            }

            // Custom validation: Check file size based on file type
            if ($this->hasFile('file')) {
                $file = $this->file('file');
                $fileSize = $file->getSize();
                $maxSizes = [
                    'image' => 5 * 1024 * 1024, // 5MB for images
                    'document' => 20 * 1024 * 1024, // 20MB for documents
                    'archive' => 50 * 1024 * 1024, // 50MB for archives
                ];

                $mimeType = $file->getClientMimeType();
                $maxSize = $maxSizes['document']; // Default

                if (strpos($mimeType, 'image/') === 0) {
                    $maxSize = $maxSizes['image'];
                } elseif (in_array($mimeType, ['application/zip', 'application/x-rar-compressed'])) {
                    $maxSize = $maxSizes['archive'];
                }

                if ($fileSize > $maxSize) {
                    $maxSizeMB = $maxSize / (1024 * 1024);
                    $validator->errors()->add('file', "File size cannot exceed {$maxSizeMB}MB for this file type.");
                }
            }
        });
    }
}
