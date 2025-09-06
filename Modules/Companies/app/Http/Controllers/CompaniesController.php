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

    public function store(CompanyRequest $request)
    {
        $user = Auth::user();
        $company = $this->companyService->createCompany($request->validated(), $user);

        return new CompanyResource($company);
    }

    public function index(Request $request)
    {
        $companies = $this->companyService->getCompanies($request->user());
        return CompanyResource::collection($companies);
    }

    public function show($id)
    {
        $company = $this->companyService->getCompanyById($id);
        return new CompanyResource($company);
    }

    public function update(CompanyRequest $request, $id)
    {
        $company = $this->companyService->updateCompany($id, $request->validated());
        return new CompanyResource($company);
    }

    public function destroy($id)
    {
        $this->companyService->deleteCompany($id);
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
