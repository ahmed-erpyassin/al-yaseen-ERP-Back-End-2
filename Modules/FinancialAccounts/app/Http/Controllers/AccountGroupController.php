<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Http\Requests\AccountGroupRequest;
use Modules\FinancialAccounts\Services\AccountGroupService;
use Modules\FinancialAccounts\Transformers\AccountGroupResource;

class AccountGroupController extends Controller
{
    protected $accountGroupService;

    public function __construct(AccountGroupService $service)
    {
        $this->accountGroupService = $service;
    }

    public function index(Request $request)
    {
        $accountGroups = $this->accountGroupService->getAccountGroup($request->user());
        return response()->json($accountGroups);
    }

    public function store(AccountGroupRequest $request)
    {
        $accountGroup = $this->accountGroupService->createAccountGroup($request->validated(), $request->user());
        return new AccountGroupResource($accountGroup);
    }

    public function show($id)
    {
        $accountGroup = $this->accountGroupService->getById($id);
        return new AccountGroupResource($accountGroup);
    }

    public function update(AccountGroupRequest $request, $id)
    {
        $accountGroup = $this->accountGroupService->updateAccountGroup($id, $request->validated());
        return new AccountGroupResource($accountGroup);
    }

    public function destroy(Request $request, $id)
    {
        $this->accountGroupService->deleteAccountGroup($id, $request->user()->id);
        return response()->json(['message' => 'Account group deleted successfully']);
    }
}
