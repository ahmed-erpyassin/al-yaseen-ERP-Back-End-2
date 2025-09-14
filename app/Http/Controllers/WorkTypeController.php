<?php

namespace App\Http\Controllers;

use App\Models\WorkType;
use Exception;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    public function all()
    {

        try {

            $types = WorkType::all();


            return response()->json([
                'success' => true,
                'data'    => $types,
            ]);
        } catch (Exception $e) {

            return response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
