<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Http\Requests\FaAttachmentRequest;
use Modules\FinancialAccounts\Services\FaAttachmentService;
use Modules\FinancialAccounts\Transformers\FaAttachmentResource;

class FaAttachmentController extends Controller
{
    protected $attachmentService;

    public function __construct(FaAttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function index(Request $request)
    {
        $attachments = $this->attachmentService->getAttachments($request->user());
        return FaAttachmentResource::collection($attachments);
    }

    public function store(FaAttachmentRequest $request)
    {
        $attachment = $this->attachmentService->createAttachment($request->validated(), $request->user());
        return new FaAttachmentResource($attachment);
    }

    public function show($id)
    {
        $attachment = $this->attachmentService->getById($id);
        return new FaAttachmentResource($attachment);
    }

    public function update(FaAttachmentRequest $request, $id)
    {
        $attachment = $this->attachmentService->updateAttachment($id, $request->validated());
        return new FaAttachmentResource($attachment);
    }

    public function destroy(Request $request, $id)
    {
        $this->attachmentService->deleteAttachment($id, $request->user()->id);
        return response()->json(['message' => 'Attachment deleted successfully']);
    }

}
