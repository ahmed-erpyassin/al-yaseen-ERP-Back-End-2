<?php

namespace Modules\FinancialAccounts\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\JournalsEntry;

class JournalEntryService
{
    public function getJournalEntries($user)
    {
        return JournalsEntry::with(['account', 'currency', 'fiscalYear'])->where('company_id', $user->company?->id)->get();
    }

    public function getJournalEntryById($id)
    {
        return JournalsEntry::with(['account', 'currency', 'fiscalYear'])->where('id', $id)->firstOrFail();
    }

    public function createJournalEntry(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $data['company_id'] ?? $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return JournalsEntry::create($data);
        });
    }

    public function updateJournalEntry($id, array $data)
    {
        $journalEntry = JournalsEntry::findOrFail($id);
        $journalEntry->update($data);
        return $journalEntry;
    }

    public function deleteJournalEntry($id, $userId)
    {
        $journalEntry = JournalsEntry::findOrFail($id);
        $journalEntry->deleted_by = $userId;
        $journalEntry->save();
        $journalEntry->delete();
    }
}
