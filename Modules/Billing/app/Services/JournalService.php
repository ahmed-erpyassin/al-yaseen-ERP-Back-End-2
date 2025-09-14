<?php

namespace Modules\Billing\Services;

use App\Support\AppContext;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Journal;

class JournalService
{
    public function getJournals($user)
    {
        return Journal::where('company_id', $user->company?->id)->get();
    }

    public function createJournal(array $data, $user): Journal
    {
        return DB::transaction(function () use ($data, $user) {
            $data = array_merge($data, AppContext::all(), [
                'created_by' => $user->id,
            ]);

            return Journal::create($data);
        });
    }

    public function getJournalById($id): Journal
    {
        return Journal::where('id', $id)->firstOrFail();
    }

    public function updateJournal($id, array $data): Journal
    {
        $journal = Journal::findOrFail($id);
        $data = array_merge($data, [
            'updated_by' => AppContext::userId(),
        ]);
        $journal->update($data);
        return $journal;
    }

    public function deleteJournal($id, $userId): void
    {
        $journal = Journal::findOrFail($id);
        $journal->deleted_by = $userId;
        $journal->save();
        $journal->delete();
    }
}
