<?php

namespace Modules\ProjectsManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ProjectsManagment\Models\Project;
use Modules\ProjectsManagment\Http\Requests\StoreProjectRequest;
use Modules\Customers\Models\Customer;
use Modules\FinancialAccounts\Models\Currency;
use Modules\Users\Models\User;
use Modules\Companies\Models\Country;
use Modules\Companies\Models\Company;

class ProjectsManagmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $projects = Project::with(['customer', 'currency', 'manager', 'country'])
                ->forCompany($companyId)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $projects,
                'message' => 'Projects retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Auto-populate customer information if customer_id is provided
            if (isset($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    $data['customer_name'] = $customer->first_name . ' ' . $customer->second_name;
                    $data['customer_email'] = $customer->email;
                    $data['customer_phone'] = $customer->phone;
                    $data['licensed_operator'] = $customer->contact_name ?? '';
                }
            }

            // Calculate VAT if needed
            if ($data['include_vat'] && isset($data['currency_price'])) {
                $company = Company::find($data['company_id']);
                if ($company && $company->vat_rate > 0) {
                    $vatAmount = $data['currency_price'] * ($company->vat_rate / 100);
                    $data['currency_price'] = $data['currency_price'] + $vatAmount;
                }
            }

            // Set additional system fields
            $data['user_id'] = $request->user()->id;
            $data['created_by'] = $request->user()->id;

            $project = Project::create($data);

            // Load relationships for response
            $project->load(['customer', 'currency', 'manager', 'country', 'company']);

            return response()->json([
                'success' => true,
                'data' => $project,
                'message' => 'Project created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $project = Project::with([
                'customer', 'currency', 'manager', 'country', 'company',
                'milestones', 'tasks', 'documents', 'financials'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $project,
                'message' => 'Project retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $data = $request->all();
            $data['updated_by'] = $request->user()->id;

            $project->update($data);
            $project->load(['customer', 'currency', 'manager', 'country', 'company']);

            return response()->json([
                'success' => true,
                'data' => $project,
                'message' => 'Project updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer data when customer is selected
     */
    public function getCustomerData($customerId): JsonResponse
    {
        try {
            $customer = Customer::with(['currency', 'country'])->findOrFail($customerId);

            return response()->json([
                'success' => true,
                'data' => [
                    'customer_name' => $customer->first_name . ' ' . $customer->second_name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'licensed_operator' => $customer->contact_name ?? '',
                    'currency_id' => $customer->currency_id,
                    'country_id' => $customer->country_id,
                ],
                'message' => 'Customer data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
    }

    /**
     * Get all customers for dropdown
     */
    public function getCustomers(Request $request): JsonResponse
    {
        try {
            $companyId = $request->user()->company_id;

            $customers = Customer::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'first_name', 'second_name', 'email', 'phone')
                ->orderBy('first_name')
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->first_name . ' ' . $customer->second_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers,
                'message' => 'Customers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all currencies for dropdown
     */
    public function getCurrencies(Request $request): JsonResponse
    {
        try {
            $companyId = $request->user()->company_id;

            $currencies = Currency::where('company_id', $companyId)
                ->select('id', 'code', 'name', 'symbol')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $currencies,
                'message' => 'Currencies retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving currencies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all employees/users for project manager dropdown
     */
    public function getEmployees(Request $request): JsonResponse
    {
        try {
            $employees = User::where('status', 'active')
                ->select('id', 'first_name', 'second_name', 'email')
                ->orderBy('first_name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->second_name,
                        'email' => $user->email,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employees retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving employees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all countries for dropdown
     */
    public function getCountries(): JsonResponse
    {
        try {
            $countries = Country::select('id', 'name', 'name_en', 'code')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $countries,
                'message' => 'Countries retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving countries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project statuses for dropdown
     */
    public function getProjectStatuses(): JsonResponse
    {
        $statuses = [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'open', 'label' => 'Open'],
            ['value' => 'on-hold', 'label' => 'On Hold'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
            ['value' => 'closed', 'label' => 'Closed'],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses,
            'message' => 'Project statuses retrieved successfully'
        ]);
    }

    /**
     * Calculate VAT for given price and company
     */
    public function calculateVAT(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
                'company_id' => 'required|exists:companies,id',
                'include_vat' => 'boolean'
            ]);

            $price = $request->price;
            $includeVat = $request->boolean('include_vat');

            if (!$includeVat) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'original_price' => $price,
                        'vat_amount' => 0,
                        'total_price' => $price,
                        'vat_rate' => 0
                    ]
                ]);
            }

            $company = Company::find($request->company_id);
            $vatRate = $company->vat_rate ?? 0;
            $vatAmount = $price * ($vatRate / 100);
            $totalPrice = $price + $vatAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'original_price' => $price,
                    'vat_amount' => $vatAmount,
                    'total_price' => $totalPrice,
                    'vat_rate' => $vatRate
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating VAT: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next project code
     */
    public function generateProjectCode(Request $request): JsonResponse
    {
        try {
            $companyId = $request->user()->company_id;
            $year = date('Y');

            $lastProject = Project::where('company_id', $companyId)
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastProject ? (intval(substr($lastProject->code, -4)) + 1) : 1;
            $code = 'PRJ-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'data' => ['code' => $code],
                'message' => 'Project code generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating project code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a project
     */
    public function destroy($id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            $project->deleted_by = auth()->id();
            $project->save();
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting project: ' . $e->getMessage()
            ], 500);
        }
    }
}
