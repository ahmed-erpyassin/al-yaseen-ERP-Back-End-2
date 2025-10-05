<?php

namespace Modules\HumanResources\app\Services\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Modules\HumanResources\Models\PayrollRecord;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\Http\Requests\Employee\PayrollRecordRequest;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\Account;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Get paginated list of payroll records
     */
    public function list(Request $request)
    {
        $query = PayrollRecord::with([
            'company',
            'currency',
            'account',
            'payrollData.employee',
            'creator'
        ])->forCompany($request->company_id);

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('payroll_number')) {
            $query->where('payroll_number', 'like', '%' . $request->payroll_number . '%');
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Create new payroll record
     */
    public function create(PayrollRecordRequest $request): PayrollRecord
    {
        $data = $request->validated();

        // Set default values
        $data['user_id'] = Auth::id();
        $data['created_by'] = Auth::id();
        $data['date'] = $data['date'] ?? now()->toDateString();

        // Get live currency rate if currency is provided
        if (isset($data['currency_id'])) {
            $data['currency_rate'] = $this->getLiveCurrencyRate($data['currency_id']);
        }

        // Auto-generate payroll number if not provided
        if (empty($data['payroll_number'])) {
            $data['payroll_number'] = $this->generatePayrollNumber($data['company_id']);
        }

        // Auto-generate salaries wages period if not provided
        if (empty($data['salaries_wages_period']) && isset($data['date'])) {
            $data['salaries_wages_period'] = $this->generateSalariesWagesPeriod($data['date']);
        }

        // Get account information if account_id is provided
        if (isset($data['account_id'])) {
            $account = Account::find($data['account_id']);
            if ($account) {
                $data['account_number'] = $account->code;
                $data['account_name'] = $account->name;
            }
        }

        return PayrollRecord::create($data);
    }

    /**
     * Show payroll record with relationships
     */
    public function show(PayrollRecord $payrollRecord): PayrollRecord
    {
        return $payrollRecord->load([
            'company',
            'user',
            'branch',
            'fiscalYear',
            'currency',
            'account',
            'payrollData.employee.jobTitle',
            'payrollData.employee.department',
            'creator',
            'updater'
        ]);
    }

    /**
     * Update payroll record
     */
    public function update(PayrollRecordRequest $request, PayrollRecord $payrollRecord): PayrollRecord
    {
        return DB::transaction(function () use ($request, $payrollRecord) {
            $data = $request->validated();
            $data['updated_by'] = Auth::id();

            // Get live currency rate if currency changed
            if (isset($data['currency_id']) && $data['currency_id'] != $payrollRecord->currency_id) {
                $data['currency_rate'] = $this->getLiveCurrencyRate($data['currency_id']);
            }

            // Update salaries wages period if date changed
            if (isset($data['date']) && $data['date'] != $payrollRecord->date->toDateString()) {
                $data['salaries_wages_period'] = $this->generateSalariesWagesPeriod($data['date']);
            }

            // Get account information if account_id changed
            if (isset($data['account_id']) && $data['account_id'] != $payrollRecord->account_id) {
                $account = Account::find($data['account_id']);
                if ($account) {
                    $data['account_number'] = $account->code;
                    $data['account_name'] = $account->name;
                }
            }

            $payrollRecord->update($data);

            // Recalculate totals
            $payrollRecord->calculateTotals();

            return $payrollRecord->fresh();
        });
    }

    /**
     * Delete payroll record
     */
    public function delete(PayrollRecord $payrollRecord): bool
    {
        return DB::transaction(function () use ($payrollRecord) {
            $payrollRecord->update(['deleted_by' => Auth::id()]);
            return $payrollRecord->delete();
        });
    }

    /**
     * Get payroll record with calculated totals
     */
    public function getWithTotals(PayrollRecord $payrollRecord): PayrollRecord
    {
        $payrollRecord->calculateTotals();
        return $this->show($payrollRecord);
    }

    /**
     * Recalculate totals for payroll record
     */
    public function recalculateTotals(PayrollRecord $payrollRecord): PayrollRecord
    {
        $payrollRecord->calculateTotals();
        return $payrollRecord->fresh();
    }

    /**
     * Generate new payroll number
     */
    public function generatePayrollNumber($companyId): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $lastRecord = PayrollRecord::where('company_id', $companyId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('payroll_number', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->payroll_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PAY{$year}{$month}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate salaries wages period text
     */
    public function generateSalariesWagesPeriod($date): string
    {
        $carbonDate = Carbon::parse($date);
        $monthName = $carbonDate->format('F');
        $year = $carbonDate->format('Y');
        return "Salaries and wages for {$monthName} / {$year}";
    }

    /**
     * Get live currency rate
     */
    public function getLiveCurrencyRate($currencyId): float
    {
        try {
            $currency = Currency::find($currencyId);

            if (!$currency) {
                return 1.0;
            }

            // If it's the base currency, return 1.0
            if ($currency->is_base_currency ?? false) {
                return 1.0;
            }

            // Cache key for this currency
            $cacheKey = "live_rate_{$currency->code}";

            // Check if we have a cached rate (cache for 5 minutes)
            $cachedRate = Cache::get($cacheKey);
            if ($cachedRate !== null) {
                return (float) $cachedRate;
            }

            // Try to fetch from external API
            $liveRate = $this->fetchFromExternalAPI($currency);

            if ($liveRate !== null) {
                // Cache the rate for 5 minutes
                Cache::put($cacheKey, $liveRate, 300);
                return $liveRate;
            }

            // Fallback to stored rate
            return $currency->exchange_rate ?? 1.0;

        } catch (\Exception $e) {
            // Fallback to 1.0 on error
            return 1.0;
        }
    }

    /**
     * Fetch rate from external API
     */
    private function fetchFromExternalAPI(Currency $currency): ?float
    {
        try {
            // Example using exchangerate-api.com (free tier)
            $baseCurrency = Currency::where('is_base_currency', true)->first();
            if (!$baseCurrency) {
                return null;
            }

            $response = Http::timeout(10)->get("https://api.exchangerate-api.com/v4/latest/{$baseCurrency->code}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['rates'][$currency->code])) {
                    return (float) $data['rates'][$currency->code];
                }
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get payroll statistics
     */
    public function getStatistics(Request $request): array
    {
        $query = PayrollRecord::forCompany($request->company_id);

        // Apply date filter if provided
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $totalRecords = $query->count();
        $totalSalaries = $query->sum('total_salaries');
        $totalTaxDeductions = $query->sum('total_income_tax_deductions');
        $totalPayableAmount = $query->sum('total_payable_amount');
        $totalPaidCash = $query->sum('total_salaries_paid_cash');

        $statusCounts = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_records' => $totalRecords,
            'total_salaries' => $totalSalaries,
            'total_tax_deductions' => $totalTaxDeductions,
            'total_payable_amount' => $totalPayableAmount,
            'total_paid_cash' => $totalPaidCash,
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * Get full preview of payroll record with all relationships
     */
    public function getFullPreview(PayrollRecord $payrollRecord): PayrollRecord
    {
        return $payrollRecord->load([
            'company',
            'user',
            'branch',
            'fiscalYear',
            'currency',
            'account',
            'payrollData' => function ($query) {
                $query->with([
                    'employee.jobTitle',
                    'employee.department',
                    'employee.manager',
                    'creator',
                    'updater'
                ])->orderBy('employee_name');
            },
            'creator',
            'updater',
            'deleter'
        ]);
    }

    /**
     * Get detailed review data for payroll record
     */
    public function getDetailedReview(PayrollRecord $payrollRecord): array
    {
        $payrollRecord = $this->getFullPreview($payrollRecord);

        // Calculate detailed statistics
        $payrollData = $payrollRecord->payrollData;

        $totalEmployees = $payrollData->count();
        $activeEmployees = $payrollData->where('status', 'active')->count();
        $inactiveEmployees = $payrollData->where('status', 'inactive')->count();

        // Salary analysis
        $totalBasicSalaries = $payrollData->sum('basic_salary');
        $totalAllowances = $payrollData->sum('allowances');
        $totalDeductions = $payrollData->sum('deductions');
        $totalIncomeTax = $payrollData->sum('income_tax');
        $totalOvertimeAmount = $payrollData->sum('overtime_amount');
        $totalSalaryForPayment = $payrollData->sum('salary_for_payment');
        $totalPaidInCash = $payrollData->sum('paid_in_cash');

        // Department breakdown
        $departmentBreakdown = $payrollData->groupBy('employee.department.name')
            ->map(function ($employees, $department) {
                return [
                    'department' => $department ?: 'No Department',
                    'employee_count' => $employees->count(),
                    'total_basic_salary' => $employees->sum('basic_salary'),
                    'total_salary_for_payment' => $employees->sum('salary_for_payment'),
                ];
            })->values();

        // Job title breakdown
        $jobTitleBreakdown = $payrollData->groupBy('job_title')
            ->map(function ($employees, $jobTitle) {
                return [
                    'job_title' => $jobTitle ?: 'No Job Title',
                    'employee_count' => $employees->count(),
                    'total_basic_salary' => $employees->sum('basic_salary'),
                    'total_salary_for_payment' => $employees->sum('salary_for_payment'),
                ];
            })->values();

        // Salary ranges
        $salaryRanges = [
            '0-1000' => $payrollData->whereBetween('basic_salary', [0, 1000])->count(),
            '1001-3000' => $payrollData->whereBetween('basic_salary', [1001, 3000])->count(),
            '3001-5000' => $payrollData->whereBetween('basic_salary', [3001, 5000])->count(),
            '5001+' => $payrollData->where('basic_salary', '>', 5000)->count(),
        ];

        return [
            'payroll_record' => $payrollRecord,
            'summary' => [
                'total_employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'inactive_employees' => $inactiveEmployees,
                'total_basic_salaries' => $totalBasicSalaries,
                'total_allowances' => $totalAllowances,
                'total_deductions' => $totalDeductions,
                'total_income_tax' => $totalIncomeTax,
                'total_overtime_amount' => $totalOvertimeAmount,
                'total_salary_for_payment' => $totalSalaryForPayment,
                'total_paid_in_cash' => $totalPaidInCash,
                'net_amount' => $totalSalaryForPayment - $totalPaidInCash,
            ],
            'breakdowns' => [
                'by_department' => $departmentBreakdown,
                'by_job_title' => $jobTitleBreakdown,
                'by_salary_range' => $salaryRanges,
            ],
            'validation' => [
                'has_employees' => $totalEmployees > 0,
                'all_employees_have_salary' => $payrollData->where('basic_salary', '<=', 0)->count() === 0,
                'totals_match' => $payrollRecord->total_salaries == $totalBasicSalaries,
            ]
        ];
    }

    /**
     * Get all payroll data with complete information
     */
    public function getAllPayrollData(PayrollRecord $payrollRecord): array
    {
        $payrollData = $payrollRecord->payrollData()
            ->with([
                'employee' => function ($query) {
                    $query->with([
                        'jobTitle',
                        'department',
                        'manager',
                        'currency',
                        'branch',
                        'fiscalYear'
                    ]);
                },
                'creator',
                'updater'
            ])
            ->orderBy('employee_name')
            ->get();

        return [
            'payroll_record' => $payrollRecord,
            'payroll_data' => $payrollData,
            'totals' => [
                'count' => $payrollData->count(),
                'total_basic_salaries' => $payrollData->sum('basic_salary'),
                'total_allowances' => $payrollData->sum('allowances'),
                'total_deductions' => $payrollData->sum('deductions'),
                'total_income_tax' => $payrollData->sum('income_tax'),
                'total_overtime_amount' => $payrollData->sum('overtime_amount'),
                'total_salary_for_payment' => $payrollData->sum('salary_for_payment'),
                'total_paid_in_cash' => $payrollData->sum('paid_in_cash'),
            ]
        ];
    }

    /**
     * Get sorted payroll records
     */
    public function getSortedPayrollRecords(Request $request): array
    {
        $query = PayrollRecord::where('company_id', $request->company_id)
            ->with(['currency', 'account', 'payrollData']);

        // Apply sorting
        $query->orderBy($request->sort_field, $request->sort_direction);

        // Apply pagination
        $perPage = $request->get('per_page', 15);
        $payrollRecords = $query->paginate($perPage);

        // Calculate totals for current page
        $currentPageData = $payrollRecords->items();
        $pageTotals = [
            'total_salaries' => collect($currentPageData)->sum('total_salaries'),
            'total_income_tax_deductions' => collect($currentPageData)->sum('total_income_tax_deductions'),
            'total_payable_amount' => collect($currentPageData)->sum('total_payable_amount'),
            'total_salaries_paid_cash' => collect($currentPageData)->sum('total_salaries_paid_cash'),
        ];

        return [
            'payroll_records' => $payrollRecords,
            'page_totals' => $pageTotals,
            'sorting' => [
                'field' => $request->sort_field,
                'direction' => $request->sort_direction
            ]
        ];
    }

    /**
     * Get first and last payroll records for navigation
     */
    public function getFirstLastPayrollRecords(Request $request): array
    {
        $query = PayrollRecord::where('company_id', $request->company_id)
            ->with(['currency', 'account']);

        // Get first record
        $firstRecord = (clone $query)->orderBy($request->sort_field, 'asc')->first();

        // Get last record
        $lastRecord = (clone $query)->orderBy($request->sort_field, 'desc')->first();

        return [
            'first_record' => $firstRecord,
            'last_record' => $lastRecord,
            'sorting' => [
                'field' => $request->sort_field,
                'direction' => $request->sort_direction
            ]
        ];
    }

    /**
     * Soft delete payroll record and its data
     */
    public function softDelete(PayrollRecord $payrollRecord): bool
    {
        // Set deleted_by before soft deleting
        $payrollRecord->deleted_by = Auth::id();
        $payrollRecord->save();

        // Soft delete all payroll data first
        $payrollRecord->payrollData()->update(['deleted_by' => Auth::id()]);
        $payrollRecord->payrollData()->delete();

        // Soft delete the payroll record
        $payrollRecord->delete();

        return true;
    }

    /**
     * Force delete payroll record and its data
     */
    public function forceDelete(PayrollRecord $payrollRecord): bool
    {
        // Force delete all payroll data first
        $payrollRecord->payrollData()->forceDelete();

        // Force delete the payroll record
        $payrollRecord->forceDelete();

        return true;
    }

    /**
     * Restore payroll record and its data
     */
    public function restore(PayrollRecord $payrollRecord): bool
    {
        // Restore the payroll record
        $payrollRecord->restore();

        // Restore all payroll data
        $payrollRecord->payrollData()->withTrashed()->restore();

        return true;
    }

    /**
     * Get deleted payroll records
     */
    public function getDeleted(Request $request): array
    {
        $query = PayrollRecord::onlyTrashed()
            ->where('company_id', $request->company_id)
            ->with(['currency', 'account', 'deleter']);

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payroll_number', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('deleted_from')) {
            $query->where('deleted_at', '>=', $request->deleted_from);
        }

        if ($request->filled('deleted_to')) {
            $query->where('deleted_at', '<=', $request->deleted_to);
        }

        $deletedRecords = $query->orderBy('deleted_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return [
            'deleted_records' => $deletedRecords,
            'summary' => [
                'total_deleted' => $deletedRecords->total(),
                'can_restore' => true,
                'can_force_delete' => true
            ]
        ];
    }
}
