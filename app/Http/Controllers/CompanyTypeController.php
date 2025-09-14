<?php

namespace App\Http\Controllers;

use App\Models\CompanyType;
use Exception;
use Illuminate\Http\Request;

class CompanyTypeController extends Controller
{
    public function all()
    {

        try {

            $types = CompanyType::all();


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
