<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Exception;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{


    public function all() {

        try {

            $currencies = Currency::all();


            return response()->json([
                'success' => true,
                'data'    => $currencies,
            ]);

        }catch(Exception $e) {

            return response([
                'success' => false,
                'message' => $e->getMessage()
            ],500);

        }

    }

}
