<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\JournalRequest;
use Modules\Billing\Services\JournalService;
use Modules\Billing\Transformers\JournalResource;

class JournalController extends Controller
{
    protected $journalService;

    public function __construct(JournalService $service)
    {
        $this->journalService = $service;
    }

    public function index(Request $request)
    {
        $journals = $this->journalService->getJournals($request->user());
        return JournalResource::collection($journals);
    }

    public function show($id)
    {
        $journal = $this->journalService->getJournalById($id);
        return new JournalResource($journal);
    }

    public function store(JournalRequest $request)
    {
        $journal = $this->journalService->createJournal($request->validated(), $request->user());
        return new JournalResource($journal);
    }

    public function update(JournalRequest $request, $id)
    {
        $journal = $this->journalService->updateJournal($id, $request->validated());
        return new JournalResource($journal);
    }

    public function destroy(Request $request, $id)
    {
        $this->journalService->deleteJournal($id, $request->user()->id);
        return response()->json(['message' => 'Journal deleted successfully']);
    }
}
