<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\IncomingOfferService;
use Modules\Purchases\Http\Requests\IncomingOfferRequest;
use Modules\Purchases\Transformers\IncomingOfferResource;

class IncomingOfferController extends Controller
{

    protected IncomingOfferService $incomingOfferService;

    public function __construct(IncomingOfferService $incomingOfferService)
    {
        $this->incomingOfferService = $incomingOfferService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->incomingOfferService->index($request);
            return response()->json([
                'success' => true,
                'data' => IncomingOfferResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingOfferRequest $request)
    {

        try {
            $offer = $this->incomingOfferService->store($request);
            return response()->json([
                'success' => true,
                'data' => new IncomingOfferResource($offer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
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
