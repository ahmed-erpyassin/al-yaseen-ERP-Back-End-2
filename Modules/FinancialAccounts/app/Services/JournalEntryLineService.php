<?php

namespace Modules\FinancialAccounts\Services;
use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\JournalsEntryLine;

class JournalEntryLineService
{
    public function getJournalEntryLines($user)
    {
        return JournalsEntryLine::with(['account', 'currency', 'fiscalYear'])->where('company_id', $user->company?->id)->get();
    }

    public function getJournalEntryLineById($id)
    {
        return JournalsEntryLine::with(['account', 'currency', 'fiscalYear'])->where('id', $id)->firstOrFail();
    }

    public function createJournalEntryLine(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return JournalsEntryLine::create($data);
        });
    }

    public function updateJournalEntryLine($id, array $data)
    {
        $journalEntryLine = JournalsEntryLine::findOrFail($id);
        $journalEntryLine->update($data);
        return $journalEntryLine;
    }

    public function deleteJournalEntryLine($id, $userId)
    {
        $journalEntryLine = JournalsEntryLine::findOrFail($id);
        $journalEntryLine->deleted_by = $userId;
        $journalEntryLine->save();
        $journalEntryLine->delete();
    }
}
