<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Sales\app\Services\OutgoingOfferService;
use Modules\Sales\Http\Requests\OutgoingOfferRequest;
use Modules\Sales\Transformers\OutgoingOfferResource;
use Modules\Sales\Models\Sale;

/**
 * @group Sales Management / Outgoing Offers
 *
 * APIs for managing outgoing sales offers, including offer creation, tracking, and conversion to orders.
 */
class OutgoingOfferController extends Controller
{

    protected OutgoingOfferService $outgoingOfferService;

    public function __construct(OutgoingOfferService $outgoingOfferService)
    {
        $this->outgoingOfferService = $outgoingOfferService;
    }

    /**
     * Get form data for creating outgoing offers
     */
    public function getFormData()
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id ?? 1;

            $data = [
                'currencies' => \Modules\FinancialAccounts\Models\Currency::where('company_id', $companyId)
                    ->select('id', 'name', 'code', 'symbol')->get(),
                'journals' => \Modules\Billing\Models\Journal::where('company_id', $companyId)
                    ->where('type', 'sales')
                    ->select('id', 'name', 'code', 'type')->get(),
                'units' => \Modules\Inventory\Models\Unit::where('company_id', $companyId)
                    ->select('id', 'name', 'code', 'symbol')->get(),
                'customers' => \Modules\Customers\Models\Customer::where('company_id', $companyId)
                    ->select('id', 'first_name', 'second_name', 'email', 'mobile')->get(),
                'branches' => \Modules\Companies\Models\Branch::where('company_id', $companyId)
                    ->select('id', 'name', 'code')->get(),
                'items' => \Modules\Inventory\Models\Item::where('company_id', $companyId)
                    ->select('id', 'name', 'item_number', 'description')->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->outgoingOfferService->index($request);
            return response()->json([
                'success' => true,
                'data' => OutgoingOfferResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching outgoing offers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OutgoingOfferRequest $request)
    {
        try {
            $offer = $this->outgoingOfferService->store($request);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($offer->load(['customer', 'currency', 'items'])),
                'message' => 'Outgoing offer created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating outgoing offer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $offer = Sale::with(['customer', 'currency', 'items', 'user', 'createdBy'])
                ->quotations()
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($offer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Outgoing offer not found.',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OutgoingOfferRequest $request, $id)
    {
        try {
            $offer = $this->outgoingOfferService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($offer->load(['customer', 'currency', 'items'])),
                'message' => 'Outgoing offer updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating outgoing offer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->outgoingOfferService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Outgoing offer deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting outgoing offer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve the specified outgoing offer.
     */
    public function approve($id)
    {
        try {
            $offer = $this->outgoingOfferService->approve($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($offer),
                'message' => 'Outgoing offer approved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while approving outgoing offer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send the specified outgoing offer.
     */
    public function send($id)
    {
        try {
            $offer = $this->outgoingOfferService->send($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($offer),
                'message' => 'Outgoing offer sent successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while sending outgoing offer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the specified outgoing offer.
     */
    public function cancel($id)
    {
        try {
            $offer = $this->outgoingOfferService->cancel($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOfferResource($offer),
                'message' => 'Outgoing offer cancelled successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while cancelling outgoing offer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
