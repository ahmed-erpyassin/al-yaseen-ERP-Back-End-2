<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreBudgetRequest;
use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $budgets = Budget::with('items')->where('user_id', $user_id)->get();
        return response()->json([
            'success' => true,
            'data'    => $budgets
        ]);
    }

    public function store(StoreBudgetRequest $request)
    {
        $data = $request->validated();

        $budget = Budget::create($data);

        if (!empty($data['items'])) {
            $budget->items()->createMany($data['items']);
        }

        return response()->json([
            'success' => true,
            'data'    => $budget->load('items')
        ], 201);
    }
}
