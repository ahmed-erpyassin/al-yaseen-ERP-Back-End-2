<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Http\Requests\JournalEntryRequest;
use Modules\FinancialAccounts\Services\JournalEntryService;
use Modules\FinancialAccounts\Transformers\JournalEntryResource;

class JournalEntriesController extends Controller
{
    protected $journalEntryService;

    public function __construct(JournalEntryService $journalEntryService)
    {
        $this->journalEntryService = $journalEntryService;
    }

    public function index(Request $request)
    {
        $journalEntries = $this->journalEntryService->getJournalEntries($request->user());
        return JournalEntryResource::collection($journalEntries);
    }

    public function store(JournalEntryRequest $request)
    {
        $journalEntry = $this->journalEntryService->createJournalEntry($request->validated(), $request->user());
        return new JournalEntryResource($journalEntry);
    }

    public function show($id)
    {
        $journalEntry = $this->journalEntryService->getJournalEntryById($id);
        return new JournalEntryResource($journalEntry);
    }

    public function update(JournalEntryRequest $request, $id)
    {
        $journalEntry = $this->journalEntryService->updateJournalEntry($id, $request->validated());
        return new JournalEntryResource($journalEntry);
    }

    public function destroy(Request $request, $id)
    {
        $this->journalEntryService->deleteJournalEntry($id, $request->user()->id);
        return response()->json(['message' => 'Journal Entry deleted successfully']);
    }
}
