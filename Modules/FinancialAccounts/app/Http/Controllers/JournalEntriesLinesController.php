<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Http\Requests\JournalEntryLineRequest;
use Modules\FinancialAccounts\Services\JournalEntryLineService;
use Modules\FinancialAccounts\Transformers\JournalEntryLineResource;

class JournalEntriesLinesController extends Controller
{
    protected $journalEntryLineService;

    public function __construct(JournalEntryLineService $journalEntryLineService)
    {
        $this->journalEntryLineService = $journalEntryLineService;
    }

    public function index(Request $request)
    {
        $journalEntryLines = $this->journalEntryLineService->getJournalEntryLines($request->user());
        return JournalEntryLineResource::collection($journalEntryLines);
    }

    public function store(JournalEntryLineRequest $request)
    {
        $journalEntryLine = $this->journalEntryLineService->createJournalEntryLine($request->validated(), $request->user());
        return new JournalEntryLineResource($journalEntryLine);
    }

    public function show($id)
    {
        $journalEntryLine = $this->journalEntryLineService->getJournalEntryLineById($id);
        return new JournalEntryLineResource($journalEntryLine);
    }

    public function update(JournalEntryLineRequest $request, $id)
    {
        $journalEntryLine = $this->journalEntryLineService->updateJournalEntryLine($id, $request->validated());
        return new JournalEntryLineResource($journalEntryLine);
    }

    public function destroy(Request $request, $id)
    {
        $this->journalEntryLineService->deleteJournalEntryLine($id, $request->user()->id);
        return response()->json(['message' => 'Journal Entry Line deleted successfully']);
    }
}
