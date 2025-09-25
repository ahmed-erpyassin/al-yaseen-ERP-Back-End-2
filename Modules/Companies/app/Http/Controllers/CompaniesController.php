<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Companies\app\Services\CompanyService;
use Modules\Companies\Http\Requests\CompanyRequest;
use Modules\Companies\Transformers\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompaniesController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(CompanyRequest $request)
    {
        $user = Auth::user();
        if ($user->company) {
            return response()->json(['message' => 'User already has a company'], 400);
        }
        $company = $this->companyService->createCompany($request->validated(), $user);
        return new CompanyResource($company);
    }

    /**
     * Display a listing of the companies.
     */
    public function index(Request $request)
    {
        $companies = $this->companyService->getCompanies($request->user());
        return CompanyResource::collection($companies);
    }

    /**
     * Display the specified company.
     */
    public function show($id)
    {
        $company = $this->companyService->getCompanyById($id);
        return new CompanyResource($company);
    }

    /**
     * Update the specified company in storage.
     */
    public function update(CompanyRequest $request, $id)
    {
        $company = $this->companyService->updateCompany($id, $request->validated());
        return new CompanyResource($company);
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy($id)
    {
        $this->companyService->deleteCompany($id);
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
