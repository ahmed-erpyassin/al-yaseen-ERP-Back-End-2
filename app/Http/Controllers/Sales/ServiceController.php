<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;

        $invoices = Service::where('user_id', $user_id)->get();
        return response()->json([
            'success' => true,
            'data'    => $invoices
        ]);
    }

    /**
     * Store new Service
     */
    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->only([
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
            'include_tax',
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
            $service->items()->create($item);
        }

        return response()->json([
            'success' => true,
            'data'    => $service,
        ], 201);
    }
}
