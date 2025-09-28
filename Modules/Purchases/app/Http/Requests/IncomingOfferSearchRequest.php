<?php

namespace Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomingOfferSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Quotation Number Search
            'quotation_number' => 'nullable|string|max:50',
            'quotation_number_from' => 'nullable|string|max:50',
            'quotation_number_to' => 'nullable|string|max:50',
            
            // Invoice Number Search
            'invoice_number' => 'nullable|string|max:50',
            
            // Supplier Search
            'supplier_name' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,id',
            
            // Date Search
            'date' => 'nullable|date',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            
            // Amount Search
            'amount' => 'nullable|numeric|min:0',
            'amount_from' => 'nullable|numeric|min:0',
            'amount_to' => 'nullable|numeric|min:0|gte:amount_from',
            
            // Currency Search
            'currency_id' => 'nullable|exists:currencies,id',
            
            // Licensed Operator Search
            'licensed_operator' => 'nullable|string|max:255',
            
            // Status Search
            'status' => 'nullable|in:draft,approved,sent,invoiced,cancelled',
            
            // Customer Search
            'customer_name' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            
            // Ledger Search
            'ledger_code' => 'nullable|string|max:50',
            'ledger_number' => 'nullable|integer|min:1',
            
            // Due Date Search
            'due_date' => 'nullable|date',
            'due_date_from' => 'nullable|date',
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
            
            // Branch Search
            'branch_id' => 'nullable|exists:branches,id',
            
            // Tax Search
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            
            // General Search
            'search' => 'nullable|string|max:255',
            
            // Sorting and Pagination
            'sort_by' => 'nullable|string|in:id,quotation_number,invoice_number,date,time,due_date,customer_number,customer_name,customer_email,customer_mobile,supplier_name,licensed_operator,ledger_code,ledger_number,status,cash_paid,checks_paid,allowed_discount,discount_percentage,discount_amount,total_without_tax,tax_percentage,tax_amount,grand_total,remaining_balance,exchange_rate,currency_rate,total_amount,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'paginate' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'quotation_number_from.string' => 'Quotation number from must be a string.',
            'quotation_number_to.string' => 'Quotation number to must be a string.',
            'date_to.after_or_equal' => 'End date must be after or equal to start date.',
            'amount_to.gte' => 'Amount to must be greater than or equal to amount from.',
            'due_date_to.after_or_equal' => 'Due date to must be after or equal to due date from.',
            'sort_by.in' => 'Invalid sort field selected.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
            'per_page.max' => 'Per page cannot exceed 100 items.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'quotation_number' => 'quotation number',
            'quotation_number_from' => 'quotation number from',
            'quotation_number_to' => 'quotation number to',
            'invoice_number' => 'invoice number',
            'supplier_name' => 'supplier name',
            'supplier_id' => 'supplier',
            'date' => 'date',
            'date_from' => 'date from',
            'date_to' => 'date to',
            'amount' => 'amount',
            'amount_from' => 'amount from',
            'amount_to' => 'amount to',
            'currency_id' => 'currency',
            'licensed_operator' => 'licensed operator',
            'status' => 'status',
            'customer_name' => 'customer name',
            'customer_id' => 'customer',
            'ledger_code' => 'ledger code',
            'ledger_number' => 'ledger number',
            'due_date' => 'due date',
            'due_date_from' => 'due date from',
            'due_date_to' => 'due date to',
            'branch_id' => 'branch',
            'tax_rate_id' => 'tax rate',
            'search' => 'search term',
            'sort_by' => 'sort field',
            'sort_order' => 'sort order',
            'per_page' => 'items per page',
        ];
    }
}
