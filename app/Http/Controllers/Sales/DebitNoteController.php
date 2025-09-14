<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreDebitNoteRequest;
use App\Models\DebitNote;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $notices = DebitNote::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data'   => $notices,
        ]);
    }

    public function store(StoreDebitNoteRequest $request)
    {
        $notice = DebitNote::create($request->validated());

        return response()->json([
            'success'  => true,
            'data'    => $notice,
        ], 201);
    }
}
