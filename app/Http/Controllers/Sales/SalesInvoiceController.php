<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesInvoiceRequest;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;

class SalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;

        $invoices = SalesInvoice::where('user_id', $user_id)->get();
        return response()->json([
            'success' => true,
            'data'    => $invoices
        ]);
    }

    /**
     * Store new invoice
     */
    public function store(StoreSalesInvoiceRequest $request)
    {
        $invoice = SalesInvoice::create($request->only([
            'company_id',
            'user_id',
            'notbook',
            'invoice_number',
            'invoice_date',
            'invoice_time',
            'due_date',
            'client_id',
            'currency_id',
            'currency_rate',
            'notes',
            'attachments',
            'cash_paid',
            'card_paid',
            'card_cash_currency',
            'allowed_discount',
            'subtotal_without_tax',
            'vat',
            'total_amount',
            'advance_paid',
        ]));

        foreach ($request->items as $item) {
            $invoice->items()->create($item);
        }

        return response()->json([
            'success' => true,
            'data'    => $invoice,
        ], 201);
    }
}
