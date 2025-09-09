<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\app\Services\JournalsFinancialService;
use Modules\FinancialAccounts\Http\Requests\JournalFinancialRequest;
use Modules\FinancialAccounts\Transformers\JournalFinancialResource;

class JournalFinancialController extends Controller
{
    protected $journalFinancialService;

    public function __construct(JournalsFinancialService $service)
    {
        $this->journalFinancialService = $service;
    }

    public function index(Request $request)
    {
        $journals = $this->journalFinancialService->getJournalFinancials($request->user());
        return JournalFinancialResource::collection($journals);
    }

    public function store(JournalFinancialRequest $request)
    {
        $journal = $this->journalFinancialService->createJournalFinancial($request->validated(), $request->user());
        return new JournalFinancialResource($journal);
    }

        public function show($id)
        {
            $journal = $this->journalFinancialService->getJournalFinancialById($id);
            return new JournalFinancialResource($journal);
        }

        public function update(JournalFinancialRequest $request, $id)
    {
        $journal = $this->journalFinancialService->updateJournalFinancial($id, $request->validated());
        return new JournalFinancialResource($journal);
    }

    public function destroy(Request $request, $id)
    {
        $this->journalFinancialService->deleteJournalFinancial($id, $request->user()->id);
        return response()->json(['message' => 'Journal deleted successfully']);
    }
}
