<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;

        $clients = Client::where('user_id', $user_id)->get();
        return response()->json([
            'success' => true,
            'data'    => $clients,
        ],200);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data'    => $client
        ], 201);
    }
}
