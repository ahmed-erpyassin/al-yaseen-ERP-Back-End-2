<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreIncomingOrderRequest;
use App\Models\IncomingOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomingOrderController extends Controller
{

    public function index(Request $request)
    {

        $user_id = $request->user()->id;

        $incomingOrders = IncomingOrder::where('user_id', $user_id)->get();
        return response()->json([
            'success' => true,
            'data'    => $incomingOrders,
        ], 200);
    }

    public function store(StoreIncomingOrderRequest $request)
    {
        try {
            DB::beginTransaction();

            $order = IncomingOrder::create($request->only([
                'company_id',
                'user_id',
                'notebook',
                'invoice_number',
                'invoice_date',
                'invoice_time',
                'due_date',
                'client_id',
                'currency',
                'currency_price',
                'include_tax',
                'allowed_discount',
                'total_without_tax',
                'tax_precentage',
                'tax_value',
                'total',
                'notes'
            ]));

            foreach ($request->items as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
