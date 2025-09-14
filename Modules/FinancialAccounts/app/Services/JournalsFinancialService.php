<?php

namespace Modules\FinancialAccounts\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialAccounts\Models\JournalsFinancial;

class JournalsFinancialService
{
    public function createJournalFinancial(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['company_id'] = $user->company?->id;
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;
            return JournalsFinancial::create($data);
        });
    }

    public function getJournalFinancials($user)
    {
        return JournalsFinancial::all();
    }

    public function getJournalFinancialById($id)
    {
        return JournalsFinancial::findOrFail($id);
    }

    public function updateJournalFinancial($id, array $data)
    {
        $journalFinancial = JournalsFinancial::findOrFail($id);
        $journalFinancial->update($data);
        return $journalFinancial;
    }

    public function deleteJournalFinancial($id, $userId)
    {
        $journalFinancial = JournalsFinancial::findOrFail($id);
        $journalFinancial->deleted_by = $userId;
        $journalFinancial->save();
        $journalFinancial->delete();
    }
}
