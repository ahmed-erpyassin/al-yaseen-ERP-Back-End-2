<?php

namespace Modules\FinancialAccounts\Services;

use Modules\FinancialAccounts\Models\FaAttachment;

class FaAttachmentService
{
    public function getAttachments($user)
    {
        return FaAttachment::where('company_id', $user->company?->id)->get();
    }

    public function getById($id)
    {
        return FaAttachment::where('id', $id)->firstOrFail();
    }

    public function createAttachment(array $data, $user)
    {
        $data['user_id'] = $user->id;
        $data['company_id'] = $data['company_id'] ?? $user->company?->id;
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        return FaAttachment::create($data);
    }

    public function updateAttachment($id, array $data)
    {
        $attachment = FaAttachment::findOrFail($id);
        $attachment->update($data);
        return $attachment;
    }

    public function deleteAttachment($id, $userId)
    {
        $attachment = FaAttachment::findOrFail($id);
        $attachment->deleted_by = $userId;
        $attachment->save();
        $attachment->delete();
    }
}
