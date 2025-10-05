<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\app\Http\Requests\ReturnInvoiceRequest;

class ReturnInvoiceService
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort field
            $allowedSortFields = [
                'id', 'created_at', 'updated_at', 'date', 'time', 'due_date',
                'total_amount', 'total_without_tax', 'tax_amount', 'status'
            ];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            $query = Purchase::query()
                ->with([
                    'supplier:id,supplier_name_ar,supplier_name_en,supplier_number,email,mobile',
                    'currency:id,code,name,symbol',
                    'creator:id,first_name,second_name,email',
                    'company:id,title',
                    'items'
                ])
                ->where('type', PurchaseTypeEnum::RETURN_INVOICE)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('supplier', function ($q) use ($customerSearch) {
                        $q->where('supplier_name_ar', 'like', '%' . $customerSearch . '%')
                          ->orWhere('supplier_name_en', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder);

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching purchase return invoices: ' . $e->getMessage());
        }
    }

    public function store(ReturnInvoiceRequest $request)
    {
        try {
            $companyId = $request->company_id;
            $userId = \Illuminate\Support\Facades\Auth::id();

            // Get validated data
            $validatedData = $request->validated();

            // Prepare data with required fields and defaults
            $data = [
                'type'       => PurchaseTypeEnum::RETURN_INVOICE,
                'company_id' => $companyId,
                'user_id'    => $userId,
                'created_by' => $userId,
                'updated_by' => $userId,
                'status'     => 'draft',

                // Provide defaults for required fields that might not be in request
                'branch_id'  => $validatedData['branch_id'] ?? 1, // Default branch
                'employee_id' => $validatedData['employee_id'] ?? null,
                'journal_id' => $validatedData['journal_id'] ?? null,
                'currency_id' => $validatedData['currency_id'] ?? 1, // Default currency

                // Financial fields with defaults
                'total_foreign' => $validatedData['total_foreign'] ?? 0,
                'total_local' => $validatedData['total_local'] ?? 0,
                'cash_paid' => $validatedData['cash_paid'] ?? 0,
                'checks_paid' => $validatedData['checks_paid'] ?? 0,
                'allowed_discount' => $validatedData['allowed_discount'] ?? 0,
                'total_without_tax' => $validatedData['total_without_tax'] ?? 0,
                'tax_percentage' => $validatedData['tax_percentage'] ?? 0,
                'tax_amount' => $validatedData['tax_amount'] ?? 0,
                'remaining_balance' => $validatedData['remaining_balance'] ?? 0,
            ] + $validatedData;

            $invoice = Purchase::create($data);

            return $invoice;
        } catch (Exception $e) {
            throw new \Exception('Error creating purchase return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific purchase return invoice with all related data
     */
    public function show($id)
    {
        try {
            $invoice = Purchase::with([
                'supplier:id,supplier_name_ar,supplier_name_en,supplier_number,email,mobile',
                'currency:id,code,name,symbol',
                'company:id,title',
                'items',
                'creator:id,first_name,second_name,email'
            ])
            ->where('type', PurchaseTypeEnum::RETURN_INVOICE)
            ->findOrFail($id);

            return $invoice;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching purchase return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase return invoice
     */
    public function update(ReturnInvoiceRequest $request, $id)
    {
        try {
            $invoice = Purchase::where('type', PurchaseTypeEnum::RETURN_INVOICE)
                ->findOrFail($id);

            $data = $request->validated();
            $data['updated_by'] = \Illuminate\Support\Facades\Auth::id();

            $invoice->update($data);

            return $invoice->load([
                'supplier', 'currency', 'company', 'items', 'creator'
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error updating purchase return invoice: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete purchase return invoice
     */
    public function destroy($id)
    {
        try {
            $invoice = Purchase::where('type', PurchaseTypeEnum::RETURN_INVOICE)
                ->findOrFail($id);

            $invoice->update(['deleted_by' => \Illuminate\Support\Facades\Auth::id()]);
            $invoice->delete();

            return ['message' => 'Purchase return invoice deleted successfully'];
        } catch (\Exception $e) {
            throw new \Exception('Error deleting purchase return invoice: ' . $e->getMessage());
        }
    }
}
