<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{

    /**
     * Upload and attach a document to any model
     */
    public function upload(
        UploadedFile $file,
        Model $relatedModel,
        $documentType = null,
        $documentNumber = null,
        string $disk = 'public'
    ): Document {

        $path = $file->store('documents', $disk);

        return $relatedModel->documents()->create([
            'document_number' => $documentNumber,
            'document_type'   => $documentType,
            'document_date'   => now(),
            'file_path'       => $path,
        ]);
    }
    public function uploadMultiple(
        array $files,
        Model $relatedModel,
        $documentType = null
    ): array {
        $documents = [];

        foreach ($files as $index => $file) {
            $documents[] = $this->upload(
                $file,
                $relatedModel,
                $documentType,
                "DOC-" . uniqid()
            );
        }

        return $documents;
    }

    public function delete(Document $document, string $disk = 'public'): void
    {
        if ($document->file_path && Storage::disk($disk)->exists($document->file_path)) {
            Storage::disk($disk)->delete($document->file_path);
        }

        $document->delete();
    }
}
