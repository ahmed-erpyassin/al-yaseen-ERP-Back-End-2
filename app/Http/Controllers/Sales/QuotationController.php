<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreQuotationRequest;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;

        $quotations = Quotation::with('items')->where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data'   => $quotations
        ]);
    }


    public function store(StoreQuotationRequest $request)
    {

        $quotation = Quotation::create($request->only([
            'company_id',
            'user_id',
            'quotation_number',
            'quotation_date',
            'expiry_date',
            'customer_name',
            'customer_phone',
            'customer_email',
            'customer_address',
            'license_number',
            'currency_id',
            'exchange_rate',
            'allowed_discount',
            'subtotal_without_tax',
            'precentage',
            'vat',
            'total',
            'notes'
        ]));

        $quotation->items()->createMany($request->items);

        return response()->json([
            'success'  => true,
            'message' => 'Quotation created successfully',
            'data'    => $quotation->load('items')
        ], 201);
    }
}
