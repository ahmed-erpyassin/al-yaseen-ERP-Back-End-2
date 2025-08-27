<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreOutgoingShipment;
use App\Models\OutgoingShipment;
use App\Models\OutgoingShipmentItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutgoingShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;

        $shipments = OutgoingShipment::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data'   => $shipments
        ]);
    }


    public function store(StoreOutgoingShipment $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $shipment = OutgoingShipment::create($request->only([
                'company_id',
                'user_id',
                'notebook',
                'invoice_number',
                'invoice_date',
                'invoice_time',
                'due_date',
                'client_id',
                'notes'
            ]));

            foreach ($request->items as $item) {
                OutgoingShipmentItem::create([
                    'outgoing_shipment_id' => $shipment->id,
                    'item_number'          => $item['item_number'],
                    'item_name'            => $item['item_name'],
                    'item_statement'       => $item['item_statement'],
                    'quantity'             => $item['quantity'],
                    'unit'                 => $item['unit'],
                    'warehouse_id'         => $item['warehouse_id'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'data'    => $shipment
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success'  => false,
                'message' => 'Error while creating outgoing shipment',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
