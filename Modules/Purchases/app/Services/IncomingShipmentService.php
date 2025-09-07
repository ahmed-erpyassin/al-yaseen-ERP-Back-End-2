<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\app\Enums\SalesTypeEnum;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;
use Modules\Purchases\Http\Requests\OutgoingShipmentRequest;
use Modules\Purchases\Models\Purchase;

class IncomingShipmentService
{
    public function index(Request $request)
    {
        try {

            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Purchase::query()
                ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('customer', function ($q) use ($customerSearch) {
                        $q->where('name', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
        }
    }

    public function store(IncomingShipmentRequest $request)
    {

        try {
            $companyId = $request->user()->company_id;
            $userId = $request->user()->id;

            $data = [
                'type'       => PurchaseTypeEnum::INCOMING_SHIPMENT,
                'company_id' => $companyId,
                'user_id'    => $userId,
                'status'     => 'draft',
            ] + $request->validated();

            $offer = Purchase::create($data);

            return $offer;
        } catch (Exception $e) {
            throw new \Exception('Error creating outgoing offer: ' . $e->getMessage());
        }
    }
}
