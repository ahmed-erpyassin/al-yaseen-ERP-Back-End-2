<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\OutgoingOfferService;
use Modules\Sales\Http\Requests\OutgoingOfferRequest;
use Modules\Sales\Transformers\OutgoingOfferResource;

class OutgoingOfferController extends Controller
{

    protected OutgoingOfferService $outgoingOfferService;

    public function __construct(OutgoingOfferService $outgoingOfferService)
    {
        $this->outgoingOfferService = $outgoingOfferService;
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
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
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
                'data' => new OutgoingOfferResource($offer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
