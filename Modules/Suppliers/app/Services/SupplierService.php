<?php

namespace Modules\Suppliers\app\Services;

use Modules\Suppliers\Http\Requests\SupplierRequest;
use Modules\Suppliers\Models\Supplier;
use Modules\Suppliers\Models\Donor;
use Modules\Suppliers\Models\SalesRepresentative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierService
{

    public function index($request)
    {
        try {

            $supplier_search = $request->get('supplier_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Supplier::query()
                ->when($supplier_search, function ($query, $supplier_search) {
                    $query->where('name', 'like', '%' . $supplier_search . '%');
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
        }
    }

    public function store(SupplierRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id ?? 101;
                $userId = $request->user()->id;

                $validatedData = $request->validated();

                // Generate supplier number if not provided
                if (empty($validatedData['supplier_number'])) {
                    $validatedData['supplier_number'] = Supplier::generateSupplierNumber();
                }

                $data = [
                    'company_id' => $companyId,
                    'user_id'    => $userId,
                    'status'     => 'active',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ] + $validatedData;

                $supplier = Supplier::create($data);

                return $supplier->load([
                    'user',
                    'company',
                    'creator',
                    'updater'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error creating supplier: ' . $e->getMessage());
        }
    }
    public function show(Supplier $supplier)
    {
        try {
            return $supplier;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching supplier: ' . $e->getMessage());
        }
    }
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {
            return DB::transaction(function () use ($request, $supplier) {
                $validatedData = $request->validated();

                // Update audit fields
                $validatedData['updated_by'] = Auth::id();

                // Handle custom classification
                if ($request->filled('custom_classification') && !empty($request->custom_classification)) {
                    $validatedData['classification'] = $request->custom_classification;
                }

                // Update supplier
                $supplier->update($validatedData);

                return $supplier->load([
                    'user',
                    'company',
                    'creator',
                    'updater'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating supplier: ' . $e->getMessage());
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting supplier: ' . $e->getMessage());
        }
    }


    public function restore(Supplier $supplier)
    {
        try {
            $supplier->restore();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error restoring supplier: ' . $e->getMessage());
        }
    }

    /**
     * Get form data for supplier creation/editing
     */
    public function getFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;

            return [
                'supplier_types' => Supplier::SUPPLIER_TYPE_OPTIONS,
                'classifications' => Supplier::CLASSIFICATION_OPTIONS,
                'status_options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive'
                ],

                // Generate next supplier number
                'next_supplier_number' => Supplier::generateSupplierNumber(),

                // Dropdown data (simplified for now - will be enhanced when relationships are ready)
                'branches' => [],
                'departments' => [],
                'projects' => [],
                'donors' => Donor::forCompany($companyId)->active()->get(['id', 'donor_number', 'donor_name_ar']),
                'sales_representatives' => SalesRepresentative::forCompany($companyId)->active()->get(['id', 'representative_number', 'first_name', 'last_name']),
                'currencies' => [],
                'barcode_types' => [],

                // Classification options for dropdown
                'classification_dropdown' => [
                    ['value' => 'major', 'label' => 'Major Suppliers'],
                    ['value' => 'medium', 'label' => 'Medium Suppliers'],
                    ['value' => 'minor', 'label' => 'Minor Suppliers'],
                ]
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error fetching form data: ' . $e->getMessage());
        }
    }

    /**
     * Search suppliers by various criteria
     */
    public function searchSuppliers(Request $request)
    {
        try {
            $query = Supplier::query();
            $companyId = $request->user()->company_id ?? 101;
            $query->where('company_id', $companyId);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_name_ar', 'like', '%' . $search . '%')
                      ->orWhere('supplier_name_en', 'like', '%' . $search . '%')
                      ->orWhere('supplier_number', 'like', '%' . $search . '%')
                      ->orWhere('supplier_code', 'like', '%' . $search . '%');
                });
            }

            return $query->active()
                        ->select('id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en', 'email', 'phone')
                        ->limit(20)
                        ->get();

        } catch (\Exception $e) {
            throw new \Exception('Error searching suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Get supplier by number
     */
    public function getSupplierByNumber(Request $request)
    {
        try {
            $supplierNumber = $request->get('supplier_number');
            $companyId = $request->user()->company_id ?? 101;

            if (!$supplierNumber) {
                throw new \Exception('Supplier number is required');
            }

            $supplier = Supplier::where('company_id', $companyId)
                              ->where('supplier_number', $supplierNumber)
                              ->active()
                              ->first();

            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            return $supplier;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching supplier by number: ' . $e->getMessage());
        }
    }

    /**
     * Get supplier by name
     */
    public function getSupplierByName(Request $request)
    {
        try {
            $supplierName = $request->get('supplier_name');
            $companyId = $request->user()->company_id ?? 101;

            if (!$supplierName) {
                throw new \Exception('Supplier name is required');
            }

            $supplier = Supplier::where('company_id', $companyId)
                              ->where(function ($q) use ($supplierName) {
                                  $q->where('supplier_name_ar', 'like', '%' . $supplierName . '%')
                                    ->orWhere('supplier_name_en', 'like', '%' . $supplierName . '%');
                              })
                              ->active()
                              ->first();

            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            return $supplier;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching supplier by name: ' . $e->getMessage());
        }
    }

    /**
     * Advanced search for suppliers with multiple criteria
     */
    public function search(Request $request)
    {
        try {
            $query = Supplier::query()
                ->with([
                    'user',
                    'company',
                    'creator',
                    'updater'
                ]);

            $companyId = $request->user()->company_id ?? 101;
            $query->where('company_id', $companyId);

            // Supplier Number range search (from/to)
            if ($request->filled('supplier_number_from')) {
                $query->where('supplier_number', '>=', $request->supplier_number_from);
            }
            if ($request->filled('supplier_number_to')) {
                $query->where('supplier_number', '<=', $request->supplier_number_to);
            }

            // Supplier Name search
            if ($request->filled('supplier_name')) {
                $query->where(function ($q) use ($request) {
                    $q->where('supplier_name_ar', 'like', '%' . $request->supplier_name . '%')
                      ->orWhere('supplier_name_en', 'like', '%' . $request->supplier_name . '%');
                });
            }

            // Date search (creation date - specific date)
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            // Date range search (creation date - from/to)
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Last Transaction Date search (specific date)
            if ($request->filled('last_transaction_date')) {
                $query->whereDate('last_transaction_date', $request->last_transaction_date);
            }

            // Last Transaction Date range search (from/to)
            if ($request->filled('last_transaction_date_from')) {
                $query->whereDate('last_transaction_date', '>=', $request->last_transaction_date_from);
            }
            if ($request->filled('last_transaction_date_to')) {
                $query->whereDate('last_transaction_date', '<=', $request->last_transaction_date_to);
            }

            // Balance search (exact or range)
            if ($request->filled('balance')) {
                $query->where('balance', $request->balance);
            }
            if ($request->filled('balance_from')) {
                $query->where('balance', '>=', $request->balance_from);
            }
            if ($request->filled('balance_to')) {
                $query->where('balance', '<=', $request->balance_to);
            }

            // Branch search
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Currency search
            if ($request->filled('currency_id')) {
                $query->where('currency_id', $request->currency_id);
            }

            // Additional filters
            if ($request->filled('supplier_type')) {
                $query->where('supplier_type', $request->supplier_type);
            }

            if ($request->filled('classification')) {
                $query->where('classification', $request->classification);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields to prevent SQL injection
            $allowedSortFields = [
                'id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en', 'supplier_type',
                'balance', 'last_transaction_date', 'classification', 'status', 'email', 'phone',
                'mobile', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100); // Limit max per page

            return $query->paginate($perPage);

        } catch (\Exception $e) {
            throw new \Exception('Error searching suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields for suppliers
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'supplier_number' => 'Supplier Number',
            'supplier_name_ar' => 'Supplier Name (Arabic)',
            'supplier_name_en' => 'Supplier Name (English)',
            'supplier_type' => 'Supplier Type',
            'balance' => 'Balance',
            'last_transaction_date' => 'Last Transaction Date',
            'classification' => 'Classification',
            'status' => 'Status',
            'email' => 'Email',
            'phone' => 'Phone',
            'mobile' => 'Mobile',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date'
        ];
    }

    /**
     * Get search form data for suppliers
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;

            return [
                'supplier_types' => Supplier::SUPPLIER_TYPE_OPTIONS,
                'classifications' => Supplier::CLASSIFICATION_OPTIONS,
                'statuses' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive']
                ],

                'sortable_fields' => $this->getSortableFields(),

                'date_ranges' => [
                    ['value' => 'today', 'label' => 'Today'],
                    ['value' => 'yesterday', 'label' => 'Yesterday'],
                    ['value' => 'this_week', 'label' => 'This Week'],
                    ['value' => 'last_week', 'label' => 'Last Week'],
                    ['value' => 'this_month', 'label' => 'This Month'],
                    ['value' => 'last_month', 'label' => 'Last Month'],
                    ['value' => 'this_year', 'label' => 'This Year'],
                    ['value' => 'custom', 'label' => 'Custom Range']
                ],

                // Dropdown data for filters
                'branches' => [], // Will be populated when branch relationships are ready
                'currencies' => [], // Will be populated when currency relationships are ready
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error fetching search form data: ' . $e->getMessage());
        }
    }

    /**
     * Get deleted suppliers (soft deleted)
     */
    public function getDeleted(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;

            $query = Supplier::onlyTrashed()
                ->with([
                    'user',
                    'company',
                    'creator',
                    'updater'
                ])
                ->where('company_id', $companyId);

            // Sorting
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            return $query->paginate($perPage);

        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted supplier
     */
    public function restoreSupplier($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $supplier = Supplier::onlyTrashed()->findOrFail($id);
                $supplier->restore();

                return [
                    'success' => true,
                    'message' => 'Supplier restored successfully',
                    'supplier' => $supplier->load(['user', 'company', 'creator', 'updater'])
                ];
            });

        } catch (\Exception $e) {
            throw new \Exception('Error restoring supplier: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a supplier (permanent delete)
     */
    public function forceDelete($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $supplier = Supplier::onlyTrashed()->findOrFail($id);
                $supplierNumber = $supplier->supplier_number;
                $supplier->forceDelete();

                return [
                    'success' => true,
                    'message' => 'Supplier permanently deleted',
                    'supplier_number' => $supplierNumber
                ];
            });

        } catch (\Exception $e) {
            throw new \Exception('Error force deleting supplier: ' . $e->getMessage());
        }
    }
}
