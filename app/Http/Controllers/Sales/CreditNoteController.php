<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreCreditNoteRequest;
use App\Models\CreditNote;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $notices = CreditNote::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data'   => $notices,
        ]);
    }

    public function store(StoreCreditNoteRequest $request)
    {
        $notice = CreditNote::create($request->validated());

        return response()->json([
            'success'  => true,
            'data'    => $notice,
        ], 201);
    }
}
