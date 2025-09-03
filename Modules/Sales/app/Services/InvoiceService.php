<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\InvoiceRequest;
use Modules\Sales\Models\Sale;

class InvoiceService
{
    public function index(Request $request)
    {
        try {

            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Sale::query()
                ->where('type', SalesTypeEnum::INVOICE)
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

    public function store(InvoiceRequest $request)
    {

        try {
            $companyId = $request->user()->company_id;
            $userId = $request->user()->id;

            $data = [
                'type'       => SalesTypeEnum::INVOICE,
                'company_id' => $companyId,
                'user_id'    => $userId,
                'status'     => 'draft',
            ] + $request->validated();

            $offer = Sale::create($data);

            return $offer;
        } catch (Exception $e) {
            throw new \Exception('Error creating outgoing offer: ' . $e->getMessage());
        }
    }
}
