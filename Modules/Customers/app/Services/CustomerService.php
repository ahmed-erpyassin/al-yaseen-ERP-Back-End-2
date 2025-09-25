<?php

namespace Modules\Customers\app\Services;

use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Models\Customer;

class CustomerService
{
    public function index($request)
    {
        try {
            $query = Customer::query()->with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ]);

            // Apply search filters
            $query = $this->applySearchFilters($query, $request);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields
            $allowedSortFields = [
                'id', 'customer_number', 'company_name', 'first_name', 'second_name',
                'email', 'phone', 'mobile', 'status', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            return $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customers: ' . $e->getMessage());
        }
    }

    public function store(CustomerRequest $request)
    {

        try {
            $companyId = $request->user()->company_id ?? $request->company_id;
            $userId = $request->user()->id;

            $data = [
                'company_id' => $companyId,
                'user_id'    => $userId,
                'status'     => 'active',
            ] + $request->validated();

            $customer = Customer::create($data);

            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error creating customer: ' . $e->getMessage());
        }
    }
    public function show(Customer $customer)
    {
        try {
            // Load all relationships for comprehensive customer data
            $customer->load([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter', 'sales', 'invoices'
            ]);

            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customer: ' . $e->getMessage());
        }
    }
    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            $data = $request->validated();

            // Add updated_by information
            $data['updated_by'] = $request->user()->id;

            // Handle company_id if not provided (use current user's company)
            if (!isset($data['company_id']) && $request->user()->company_id) {
                $data['company_id'] = $request->user()->company_id;
            }

            // Ensure status is maintained if not provided
            if (!isset($data['status'])) {
                $data['status'] = $customer->status;
            }

            // Update the customer with all provided data
            $customer->update($data);

            // Reload the customer with relationships for response
            $customer->load([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ]);

            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error updating customer: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer, $userId = null)
    {
        try {
            // Add deleted_by information before soft delete
            $customer->update(['deleted_by' => $userId]);
            $customer->delete();
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting customer: ' . $e->getMessage());
        }
    }


    public function restore(Customer $customer)
    {
        try {
            $customer->restore();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error restoring customer: ' . $e->getMessage());
        }
    }

    public function findById($id)
    {
        try {
            return Customer::findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Customer not found: ' . $e->getMessage());
        }
    }

    public function getCustomersWithPagination($request, $perPage = 10)
    {
        try {
            $query = Customer::query()->with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ]);

            // Apply search filters
            $query = $this->applySearchFilters($query, $request);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort fields
            $allowedSortFields = [
                'id', 'customer_number', 'company_name', 'first_name', 'second_name',
                'email', 'phone', 'mobile', 'status', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customers with pagination: ' . $e->getMessage());
        }
    }

    /**
     * Apply comprehensive search filters to the customer query.
     */
    private function applySearchFilters($query, $request)
    {
        // Basic search (legacy support)
        $customerSearch = $request->get('customerSearch', null);
        if ($customerSearch) {
            $query->byName($customerSearch);
        }

        // Customer Number Range Search
        $customerNumberFrom = $request->get('customer_number_from', null);
        $customerNumberTo = $request->get('customer_number_to', null);
        $query->byCustomerNumberRange($customerNumberFrom, $customerNumberTo);

        // Customer Name Search
        $customerName = $request->get('customer_name', null);
        $query->byName($customerName);

        // Sales Representative Filter
        $salesRepresentative = $request->get('sales_representative', null);
        $query->bySalesRepresentative($salesRepresentative);

        // Currency Filter
        $currency = $request->get('currency_id', null);
        $query->byCurrency($currency);

        // Last Transaction Date Filters
        $lastTransactionDate = $request->get('last_transaction_date', null);
        $lastTransactionDateFrom = $request->get('last_transaction_date_from', null);
        $lastTransactionDateTo = $request->get('last_transaction_date_to', null);

        $query->byLastTransactionDate($lastTransactionDate, $lastTransactionDateFrom, $lastTransactionDateTo);

        // Status Filter
        $status = $request->get('status', null);
        if ($status && in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }

        // Company Filter
        $companyId = $request->get('company_id', null);
        if ($companyId) {
            $query->forCompany($companyId);
        }

        // Email Search
        $email = $request->get('email', null);
        if ($email) {
            $query->where('email', 'like', '%' . $email . '%');
        }

        // Phone Search
        $phone = $request->get('phone', null);
        if ($phone) {
            $query->where(function ($q) use ($phone) {
                $q->where('phone', 'like', '%' . $phone . '%')
                  ->orWhere('mobile', 'like', '%' . $phone . '%');
            });
        }

        // Category Filter
        $category = $request->get('category', null);
        if ($category) {
            $query->where('category', $category);
        }

        // Country Filter
        $countryId = $request->get('country_id', null);
        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        // Region Filter
        $regionId = $request->get('region_id', null);
        if ($regionId) {
            $query->where('region_id', $regionId);
        }

        // City Filter
        $cityId = $request->get('city_id', null);
        if ($cityId) {
            $query->where('city_id', $cityId);
        }

        // Date Range Filters
        $createdFrom = $request->get('created_from', null);
        $createdTo = $request->get('created_to', null);

        if ($createdFrom) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        return $query;
    }

    public function bulkDelete($customerIds, $userId = null)
    {
        try {
            $customers = Customer::whereIn('id', $customerIds)->get();

            foreach ($customers as $customer) {
                $customer->update(['deleted_by' => $userId]);
                $customer->delete();
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error bulk deleting customers: ' . $e->getMessage());
        }
    }

    /**
     * Get customers with comprehensive search and sorting.
     */
    public function getCustomersAdvanced($filters = [], $sortBy = 'created_at', $sortOrder = 'desc', $perPage = null)
    {
        try {
            $query = Customer::query()->with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ]);

            // Apply filters
            foreach ($filters as $key => $value) {
                if ($value !== null && $value !== '') {
                    switch ($key) {
                        case 'customer_number_from':
                            $query->where('customer_number', '>=', $value);
                            break;
                        case 'customer_number_to':
                            $query->where('customer_number', '<=', $value);
                            break;
                        case 'customer_name':
                            $query->byName($value);
                            break;
                        case 'sales_representative':
                            $query->bySalesRepresentative($value);
                            break;
                        case 'currency_id':
                            $query->byCurrency($value);
                            break;
                        case 'last_transaction_date':
                            $query->byLastTransactionDate($value);
                            break;
                        case 'last_transaction_date_from':
                            if (isset($filters['last_transaction_date_to'])) {
                                $query->byLastTransactionDate(null, $value, $filters['last_transaction_date_to']);
                            } else {
                                $query->byLastTransactionDate(null, $value);
                            }
                            break;
                        case 'status':
                            $query->where('status', $value);
                            break;
                        case 'company_id':
                            $query->forCompany($value);
                            break;
                        default:
                            if (in_array($key, ['email', 'phone', 'mobile', 'category', 'country_id', 'region_id', 'city_id'])) {
                                if (in_array($key, ['email', 'phone', 'mobile', 'category'])) {
                                    $query->where($key, 'like', '%' . $value . '%');
                                } else {
                                    $query->where($key, $value);
                                }
                            }
                            break;
                    }
                }
            }

            // Apply sorting
            $allowedSortFields = [
                'id', 'customer_number', 'company_name', 'first_name', 'second_name',
                'email', 'phone', 'mobile', 'status', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Return paginated or all results
            if ($perPage) {
                return $query->paginate($perPage);
            } else {
                return $query->get();
            }
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customers with advanced filters: ' . $e->getMessage());
        }
    }

    /**
     * Get customer statistics with transaction data.
     */
    public function getCustomerStats()
    {
        try {
            $stats = [
                'total_customers' => Customer::count(),
                'active_customers' => Customer::where('status', 'active')->count(),
                'inactive_customers' => Customer::where('status', 'inactive')->count(),
                'deleted_customers' => Customer::onlyTrashed()->count(),
                'customers_this_month' => Customer::whereMonth('created_at', now()->month)->count(),
                'customers_this_year' => Customer::whereYear('created_at', now()->year)->count(),
                'customers_with_sales' => Customer::whereHas('sales')->count(),
                'customers_with_invoices' => Customer::whereHas('invoices')->count(),
                'customers_by_status' => Customer::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'customers_by_category' => Customer::selectRaw('category, COUNT(*) as count')
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->pluck('count', 'category'),
            ];

            return $stats;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customer statistics: ' . $e->getMessage());
        }
    }
}
