<?php

namespace Modules\FinancialAccounts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FinancialAccounts\Http\Requests\AccountRequest;
use Modules\FinancialAccounts\Services\AccountService;
use Modules\FinancialAccounts\Transformers\AccountResource;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $service)
    {
        $this->accountService = $service;
    }

    public function index(Request $request)
    {
        $accounts = $this->accountService->getAccounts($request->user());
        return AccountResource::collection($accounts);
    }

    public function store(AccountRequest $request)
    {
        $account = $this->accountService->createAccount($request->validated(), $request->user());
        return new AccountResource($account);
    }

    public function show($id)
    {
        $account = $this->accountService->getAccountById($id);
        return new AccountResource($account);
    }

    public function update(AccountRequest $request, $id)
    {
        $account = $this->accountService->updateAccount($id, $request->validated());
        return new AccountResource($account);
    }

    public function destroy(Request $request, $id)
    {
        $this->accountService->deleteAccount($id, $request->user()->id);
        return response()->json(['message' => 'Account deleted successfully']);
    }
}
